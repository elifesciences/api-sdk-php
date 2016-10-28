<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

abstract class ArticleVersionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $articlesClient;
    private $found = [];
    private $globalCallback;

    public function __construct(ArticlesClient $articlesClient)
    {
        $this->articlesClient = $articlesClient;
    }

    /**
     * Selects the Model class from the 'type' and 'status' fields
     *
     * @param string|null $status
     * @return string|null
     */
    public static function articleClass(string $type, $status)
    {
        switch ($type) {
            case 'correction':
            case 'editorial':
            case 'feature':
            case 'insight':
            case 'research-advance':
            case 'research-article':
            case 'research-exchange':
            case 'retraction':
            case 'registered-report':
            case 'replication-study':
            case 'short-report':
            case 'tools-resources':
                if ('poa' === $status) {
                    $class = ArticlePoA::class;
                } else {
                    $class = ArticleVoR::class;
                }
                return $class;
        }

        return null;
    }

    final public function denormalize($data, $class, $format = null, array $context = []) : ArticleVersion
    {
        if (!empty($context['snippet'])) {
            $complete = $this->denormalizeSnippet($data);

            $data['abstract'] = $complete
                ->then(function (Result $article) {
                    return $article['abstract'] ?? null;
                });

            $data['authors'] = new PromiseSequence($complete
                ->then(function (Result $article) {
                    return $article['authors'];
                }));

            $data['copyright'] = $complete
                ->then(function (Result $article) {
                    return $article['copyright'];
                });

            $data['issue'] = $complete
                ->then(function (Result $article) {
                    return $article['issue'] ?? null;
                });
        } else {
            $complete = null;

            $data['abstract'] = promise_for($data['abstract'] ?? null);

            $data['authors'] = new ArraySequence($data['authors']);

            $data['copyright'] = promise_for($data['copyright']);

            $data['issue'] = promise_for($data['issue'] ?? null);
        }

        $data['abstract'] = $data['abstract']
            ->then(function ($abstract) use ($format, $context) {
                if (empty($abstract)) {
                    return null;
                }

                return new ArticleSection(
                    new ArraySequence(array_map(function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    }, $abstract['content'])),
                    $abstract['doi'] ?? null
                );
            });

        $data['authors'] = $data['authors']->map(function (array $author) use ($format, $context) {
            return $this->denormalizer->denormalize($author, AuthorEntry::class, $format, $context);
        });

        $data['copyright'] = $data['copyright']
            ->then(function (array $copyright) {
                return new Copyright($copyright['license'], $copyright['statement'], $copyright['holder'] ?? null);
            });

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            $context['snippet'] = true;

            return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
        }, $data['subjects'] ?? []));

        return $this->denormalizeArticle($data, $complete, $format, $context);
    }

    private function denormalizeSnippet(array $article) : PromiseInterface
    {
        if (isset($this->found[$article['id']])) {
            return $this->found[$article['id']];
        }

        $this->found[$article['id']] = null;

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                foreach ($this->found as $id => $article) {
                    if (null === $article) {
                        $this->found[$id] = $this->articlesClient->getArticleLatestVersion(
                            [
                                'Accept' => [
                                    new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 1),
                                    new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 1),
                                ],
                            ],
                            $id
                        );
                    }
                }

                $this->globalCallback = null;

                return all($this->found)->wait();
            });
        }

        return $this->globalCallback
            ->then(function (array $articles) use ($article) {
                return $articles[$article['id']];
            });
    }

    /**
     * @param ArticleVersion $object
     */
    final public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'version' => $object->getVersion(),
            'type' => $object->getType(),
            'doi' => $object->getDoi(),
            'authorLine' => $object->getAuthorLine(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(DATE_ATOM),
            'statusDate' => $object->getStatusDate()->format(DATE_ATOM),
            'volume' => $object->getVolume(),
            'elocationId' => $object->getElocationId(),
        ];

        if ($object->getTitlePrefix()) {
            $data['titlePrefix'] = $object->getTitlePrefix();
        }

        if ($object->getPdf()) {
            $data['pdf'] = $object->getPdf();
        }

        if (count($object->getSubjects()) > 0) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        if (!empty($object->getResearchOrganisms())) {
            $data['researchOrganisms'] = $object->getResearchOrganisms();
        }

        if (empty($context['snippet'])) {
            $data['copyright'] = [
                'license' => $object->getCopyright()->getLicense(),
                'statement' => $object->getCopyright()->getStatement(),
            ];

            if ($object->getCopyright()->getHolder()) {
                $data['copyright']['holder'] = $object->getCopyright()->getHolder();
            }

            $data['authors'] = $object->getAuthors()->map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            })->toArray();

            if ($object->getIssue()) {
                $data['issue'] = $object->getIssue();
            }

            if ($object->getAbstract()) {
                $data['abstract'] = [
                    'content' => $object->getAbstract()->getContent()->map(function (Block $block) use (
                        $format,
                        $context
                    ) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray(),
                ];

                if ($object->getAbstract()->getDoi()) {
                    $data['abstract']['doi'] = $object->getAbstract()->getDoi();
                }
            }
        }

        return $this->normalizeArticle($object, $data, $format, $context);
    }

    abstract protected function denormalizeArticle(
        $data,
        PromiseInterface $article = null,
        $class,
        $format = null,
        array $context = []
    ) : ArticleVersion;

    abstract protected function normalizeArticle(
        ArticleVersion $article,
        array $data,
        $format = null,
        array $context = []
    ) : array;
}
