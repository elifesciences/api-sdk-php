<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\ArticlesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Articles;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
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
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reviewer;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

abstract class ArticleVersionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

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
                            new MediaType(ArticlesClient::TYPE_ARTICLE_POA, Articles::VERSION_ARTICLE_POA),
                            new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, Articles::VERSION_ARTICLE_VOR),
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
            case 'expression-concern':
            case 'feature':
            case 'insight':
            case 'research-advance':
            case 'research-article':
            case 'research-communication':
            case 'retraction':
            case 'registered-report':
            case 'replication-study':
            case 'review-article':
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

    final public function denormalize($data, $type, $format = null, array $context = []) : ArticleVersion
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
                            }, $award['recipients'] ?? [])),
                            $award['awardDoi'] ?? null
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

        if (false === empty($data['image']['thumbnail'])) {
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class,
                $format, $context);
        }

        if (false === empty($data['image']['social'])) {
            $data['image']['social'] = $this->denormalizer->denormalize($data['image']['social'], Image::class,
                $format, $context);
        }

        $data['published'] = !empty($data['published']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']) : null;
        $data['versionDate'] = !empty($data['versionDate']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['versionDate']) : null;
        $data['statusDate'] = !empty($data['statusDate']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['statusDate']) : null;

        return $this->denormalizeArticle($data, $complete, $type, $format, $context);
    }

    /**
     * @param ArticleVersion $data
     */
    final public function normalize($data, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $arr = [
            'id' => $data->getId(),
            'stage' => $data->getStage(),
            'version' => $data->getVersion(),
            'type' => $data->getType(),
            'doi' => $data->getDoi(),
            'title' => $data->getTitle(),
            'volume' => $data->getVolume(),
            'elocationId' => $data->getElocationId(),
        ];

        if ($data->getPublishedDate()) {
            $arr['published'] = $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT);
        }
        if ($data->getVersionDate()) {
            $arr['versionDate'] = $data->getVersionDate()->format(ApiSdk::DATE_FORMAT);
        }
        if ($data->getStatusDate()) {
            $arr['statusDate'] = $data->getStatusDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getTitlePrefix()) {
            $arr['titlePrefix'] = $data->getTitlePrefix();
        }

        if ($data->getAuthorLine()) {
            $arr['authorLine'] = $data->getAuthorLine();
        }

        if ($data->getPdf()) {
            $arr['pdf'] = $data->getPdf();
        }

        if (!$data->getSubjects()->isEmpty()) {
            $arr['subjects'] = $data->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        if (!empty($data->getResearchOrganisms())) {
            $arr['researchOrganisms'] = $data->getResearchOrganisms();
        }

        if ($data->getAbstract()) {
            $arr['abstract'] = [
                'content' => $data->getAbstract()->getContent()->map(function (Block $block) use (
                    $format,
                    $context
                ) {
                    return $this->normalizer->normalize($block, $format, $context);
                })->toArray(),
            ];

            if ($data->getAbstract()->getDoi()) {
                $arr['abstract']['doi'] = $data->getAbstract()->getDoi();
            }
        }

        if ($data->getThumbnail()) {
            $arr['image']['thumbnail'] = $this->normalizer->normalize($data->getThumbnail(), $format, $context);
        }

        if ($data->getSocialImage()) {
            $arr['image']['social'] = $this->normalizer->normalize($data->getSocialImage(), $format, $context);
        }

        if (empty($context['snippet'])) {
            if ($data->getXml()) {
                $arr['xml'] = $data->getXml();
            }

            $arr['copyright'] = [
                'license' => $data->getCopyright()->getLicense(),
                'statement' => $data->getCopyright()->getStatement(),
            ];

            if ($data->getCopyright()->getHolder()) {
                $arr['copyright']['holder'] = $data->getCopyright()->getHolder();
            }

            if ($data->getAuthors()->notEmpty()) {
                $arr['authors'] = $data->getAuthors()->map(function (AuthorEntry $author) use ($format, $context) {
                    return $this->normalizer->normalize($author, $format, ['type' => true] + $context);
                })->toArray();
            }

            if ($data->getReviewers()->notEmpty()) {
                $arr['reviewers'] = $data->getReviewers()->map(function (Reviewer $reviewer) use ($format, $context) {
                    return $this->normalizer->normalize($reviewer, $format, $context);
                })->toArray();
            }

            if ($data->getIssue()) {
                $arr['issue'] = $data->getIssue();
            }

            if (!$data->getEthics()->isEmpty()) {
                $arr['ethics'] = $data->getEthics()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if ($data->getFunding()) {
                if ($data->getFunding()->getAwards()->notEmpty()) {
                    $arr['funding']['awards'] = $data->getFunding()->getAwards()
                        ->map(function (FundingAward $award) use ($format, $context) {
                            $source = $this->normalizer->normalize($award->getSource()->getPlace(), $format, $context);
                            if ($award->getSource()->getFunderId()) {
                                $source['funderId'] = $award->getSource()->getFunderId();
                            }

                            $data = [
                                'id' => $award->getId(),
                                'source' => $source,
                            ];

                            if ($award->getRecipients()->notEmpty()) {
                                $data['recipients'] = $award->getRecipients()
                                    ->map(function (Author $author) use ($format, $context) {
                                        return $this->normalizer->normalize(
                                            $author,
                                            $format,
                                            ['type' => true] + $context
                                        );
                                    })->toArray();
                            }

                            if ($award->getAwardId()) {
                                $data['awardId'] = $award->getAwardId();
                            }

                            if ($award->getAwardDoi()) {
                                $data['awardDoi'] = $award->getAwardDoi();
                            }

                            return $data;
                        })->toArray();
                }
                $arr['funding']['statement'] = $data->getFunding()->getStatement();
            }

            if ($data->getDataAvailability()->notEmpty()) {
                $arr['dataSets']['availability'] = $data->getDataAvailability()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if ($data->getGeneratedDataSets()->notEmpty()) {
                $arr['dataSets']['generated'] = $data->getGeneratedDataSets()
                    ->map(function (DataSet $dataSet) use ($format, $context) {
                        return $this->normalizer->normalize($dataSet, $format, $context);
                    })->toArray();
            }

            if ($data->getUsedDataSets()->notEmpty()) {
                $arr['dataSets']['used'] = $data->getUsedDataSets()
                    ->map(function (DataSet $dataSet) use ($format, $context) {
                        return $this->normalizer->normalize($dataSet, $format, $context);
                    })->toArray();
            }

            if ($data->getAdditionalFiles()->notEmpty()) {
                $arr['additionalFiles'] = $data->getAdditionalFiles()
                    ->map(function (AssetFile $file) use ($format, $context) {
                        return $this->normalizer->normalize($file, $format, $context);
                    })->toArray();
            }
        }

        return $this->normalizeArticle($data, $arr, $format, $context);
    }

    /**
     * @param $type
     * @return bool
     */
    final protected function isArticleType($type)
    {
        return in_array($type, [
            'correction',
            'editorial',
            'expression-concern',
            'feature',
            'insight',
            'research-advance',
            'research-article',
            'research-communication',
            'retraction',
            'registered-report',
            'replication-study',
            'review-article',
            'scientific-correspondence',
            'short-report',
            'tools-resources',
        ], true);
    }

    /**
     * @param $data
     * @param PromiseInterface|null $article
     * @param string $class
     * @param string|null $format
     * @param array $context
     * @return ArticleVersion
     */
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
