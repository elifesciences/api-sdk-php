<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\Funder;
use eLife\ApiSdk\Model\Funding;
use eLife\ApiSdk\Model\FundingAward;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reviewer;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

abstract class ArticleVersionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(ArticlesClient $articlesClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $article) : string {
                return $article['id'].'.'.$article['version'];
            },
            function (string $id) use ($articlesClient) : PromiseInterface {
                list($id, $version) = explode('.', $id);

                return $articlesClient->getArticleVersion(
                    [
                        'Accept' => implode(', ', [
                            new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 2),
                            new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 2),
                        ]),
                    ],
                    $id,
                    $version
                );
            }
        );
    }

    /**
     * Selects the Model class from the 'type' and 'status' fields.
     *
     * @return string|null
     */
    public static function articleClass(string $type, string $status = null)
    {
        switch ($type) {
            case 'correction':
            case 'editorial':
            case 'feature':
            case 'insight':
            case 'research-advance':
            case 'research-article':
            case 'research-communication':
            case 'retraction':
            case 'registered-report':
            case 'replication-study':
            case 'scientific-correspondence':
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
            $complete = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['additionalFiles'] = new PromiseSequence($complete
                ->then(function (Result $article) {
                    return $article['additionalFiles'] ?? [];
                }));

            $data['authors'] = new PromiseSequence($complete
                ->then(function (Result $article) {
                    return $article['authors'] ?? [];
                }));

            $data['copyright'] = $complete
                ->then(function (Result $article) {
                    return $article['copyright'];
                });

            $data['dataSets'] = $complete
                ->then(function (Result $article) {
                    return $article['dataSets'] ?? null;
                });

            $data['ethics'] = new PromiseSequence($complete
                ->then(function (Result $article) {
                    return $article['ethics'] ?? [];
                }));

            $data['funding'] = $complete
                ->then(function (Result $article) {
                    return $article['funding'] ?? null;
                });

            $data['issue'] = $complete
                ->then(function (Result $article) {
                    return $article['issue'] ?? null;
                });

            $data['reviewers'] = new PromiseSequence($complete
                ->then(function (Result $article) {
                    return $article['reviewers'] ?? [];
                }));

            $data['xml'] = $complete
                ->then(function (Result $article) {
                    return $article['xml'] ?? null;
                });
        } else {
            $complete = null;

            $data['additionalFiles'] = new ArraySequence($data['additionalFiles'] ?? []);

            $data['authors'] = new ArraySequence($data['authors'] ?? []);

            $data['copyright'] = promise_for($data['copyright']);

            $data['dataSets'] = promise_for($data['dataSets'] ?? null);

            $data['ethics'] = new ArraySequence($data['ethics'] ?? []);

            $data['funding'] = promise_for($data['funding'] ?? null);

            $data['issue'] = promise_for($data['issue'] ?? null);

            $data['reviewers'] = new ArraySequence($data['reviewers'] ?? []);

            $data['xml'] = promise_for($data['xml'] ?? null);
        }

        if (!empty($data['abstract'])) {
            $data['abstract'] = new ArticleSection(
                new ArraySequence(array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $data['abstract']['content'])),
                $data['abstract']['doi'] ?? null
            );
        }

        $data['additionalFiles'] = $data['additionalFiles']->map(function (array $file) use ($format, $context) {
            return $this->denormalizer->denormalize($file, AssetFile::class, $format, $context);
        });

        $data['authors'] = $data['authors']->map(function (array $author) use ($format, $context) {
            return $this->denormalizer->denormalize($author, AuthorEntry::class, $format, $context);
        });

        $data['copyright'] = $data['copyright']
            ->then(function (array $copyright) {
                return new Copyright($copyright['license'], $copyright['statement'], $copyright['holder'] ?? null);
            });

        $data['ethics'] = $data['ethics']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['dataAvailability'] = new PromiseSequence($data['dataSets']
            ->then(function (array $dataSets = null) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $dataSets['availability'] ?? []);
            }));

        $data['generatedDataSets'] = new PromiseSequence($data['dataSets']
            ->then(function (array $dataSets = null) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, DataSet::class, $format, $context);
                }, $dataSets['generated'] ?? []);
            }));

        $data['usedDataSets'] = new PromiseSequence($data['dataSets']
            ->then(function (array $dataSets = null) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, DataSet::class, $format, $context);
                }, $dataSets['used'] ?? []);
            }));

        $data['funding'] = $data['funding']
            ->then(function (array $funding = null) use ($format, $context) {
                if (empty($funding)) {
                    return null;
                }

                return new Funding(
                    new ArraySequence(array_map(function (array $award) use ($format, $context) {
                        return new FundingAward(
                            $award['id'],
                            new Funder(
                                $this->denormalizer->denormalize($award['source'], Place::class, $format, $context),
                                $award['source']['funderId'] ?? null
                            ),
                            $award['awardId'] ?? null,
                            new ArraySequence(array_map(function (array $recipient) use ($format, $context) {
                                return $this->denormalizer->denormalize($recipient, Author::class, $format, $context);
                            }, $award['recipients']))
                        );
                    }, $funding['awards'] ?? [])),
                    $funding['statement']
                );
            });

        $data['reviewers'] = $data['reviewers']->map(function (array $reviewer) use ($format, $context) {
            return $this->denormalizer->denormalize($reviewer, Reviewer::class, $format, $context);
        });

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            $context['snippet'] = true;

            return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
        }, $data['subjects'] ?? []));

        $data['published'] = !empty($data['published']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']) : null;
        $data['versionDate'] = !empty($data['versionDate']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['versionDate']) : null;
        $data['statusDate'] = !empty($data['statusDate']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['statusDate']) : null;

        return $this->denormalizeArticle($data, $complete, $class, $format, $context);
    }

    /**
     * @param ArticleVersion $object
     */
    final public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [
            'id' => $object->getId(),
            'stage' => $object->getStage(),
            'version' => $object->getVersion(),
            'type' => $object->getType(),
            'doi' => $object->getDoi(),
            'title' => $object->getTitle(),
            'volume' => $object->getVolume(),
            'elocationId' => $object->getElocationId(),
        ];

        if ($object->getPublishedDate()) {
            $data['published'] = $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT);
        }
        if ($object->getVersionDate()) {
            $data['versionDate'] = $object->getVersionDate()->format(ApiSdk::DATE_FORMAT);
        }
        if ($object->getStatusDate()) {
            $data['statusDate'] = $object->getStatusDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getTitlePrefix()) {
            $data['titlePrefix'] = $object->getTitlePrefix();
        }

        if ($object->getAuthorLine()) {
            $data['authorLine'] = $object->getAuthorLine();
        }

        if ($object->getPdf()) {
            $data['pdf'] = $object->getPdf();
        }

        if (!$object->getSubjects()->isEmpty()) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        if (!empty($object->getResearchOrganisms())) {
            $data['researchOrganisms'] = $object->getResearchOrganisms();
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

        if (empty($context['snippet'])) {
            if ($object->getXml()) {
                $data['xml'] = $object->getXml();
            }

            $data['copyright'] = [
                'license' => $object->getCopyright()->getLicense(),
                'statement' => $object->getCopyright()->getStatement(),
            ];

            if ($object->getCopyright()->getHolder()) {
                $data['copyright']['holder'] = $object->getCopyright()->getHolder();
            }

            if ($object->getAuthors()->notEmpty()) {
                $data['authors'] = $object->getAuthors()->map(function (AuthorEntry $author) use ($format, $context) {
                    return $this->normalizer->normalize($author, $format, $context);
                })->toArray();
            }

            if ($object->getReviewers()->notEmpty()) {
                $data['reviewers'] = $object->getReviewers()->map(function (Reviewer $reviewer) use ($format, $context) {
                    return $this->normalizer->normalize($reviewer, $format, $context);
                })->toArray();
            }

            if ($object->getIssue()) {
                $data['issue'] = $object->getIssue();
            }

            if (!$object->getEthics()->isEmpty()) {
                $data['ethics'] = $object->getEthics()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if ($object->getFunding()) {
                if ($object->getFunding()->getAwards()->notEmpty()) {
                    $data['funding']['awards'] = $object->getFunding()->getAwards()
                        ->map(function (FundingAward $award) use ($format, $context) {
                            $source = $this->normalizer->normalize($award->getSource()->getPlace(), $format, $context);
                            if ($award->getSource()->getFunderId()) {
                                $source['funderId'] = $award->getSource()->getFunderId();
                            }

                            $data = [
                                'id' => $award->getId(),
                                'source' => $source,
                                'recipients' => $award->getRecipients()
                                    ->map(function (Author $author) use ($format, $context) {
                                        return $this->normalizer->normalize($author, $format, $context);
                                    })->toArray(),
                            ];

                            if ($award->getAwardId()) {
                                $data['awardId'] = $award->getAwardId();
                            }

                            return $data;
                        })->toArray();
                }
                $data['funding']['statement'] = $object->getFunding()->getStatement();
            }

            if ($object->getDataAvailability()->notEmpty()) {
                $data['dataSets']['availability'] = $object->getDataAvailability()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if ($object->getGeneratedDataSets()->notEmpty()) {
                $data['dataSets']['generated'] = $object->getGeneratedDataSets()
                    ->map(function (DataSet $dataSet) use ($format, $context) {
                        return $this->normalizer->normalize($dataSet, $format, $context);
                    })->toArray();
            }

            if ($object->getUsedDataSets()->notEmpty()) {
                $data['dataSets']['used'] = $object->getUsedDataSets()
                    ->map(function (DataSet $dataSet) use ($format, $context) {
                        return $this->normalizer->normalize($dataSet, $format, $context);
                    })->toArray();
            }

            if ($object->getAdditionalFiles()->notEmpty()) {
                $data['additionalFiles'] = $object->getAdditionalFiles()
                    ->map(function (AssetFile $file) use ($format, $context) {
                        return $this->normalizer->normalize($file, $format, $context);
                    })->toArray();
            }
        }

        return $this->normalizeArticle($object, $data, $format, $context);
    }

    final protected function isArticleType($type)
    {
        return in_array($type, [
            'correction',
            'editorial',
            'feature',
            'insight',
            'research-advance',
            'research-article',
            'research-communication',
            'retraction',
            'registered-report',
            'replication-study',
            'scientific-correspondence',
            'short-report',
            'tools-resources',
        ], true);
    }

    abstract protected function denormalizeArticle(
        $data,
        PromiseInterface $article = null,
        string $class,
        string $format = null,
        array $context = []
    ) : ArticleVersion;

    abstract protected function normalizeArticle(
        ArticleVersion $article,
        array $data,
        string $format = null,
        array $context = []
    ) : array;
}
