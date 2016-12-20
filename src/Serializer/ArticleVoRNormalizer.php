<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Funder;
use eLife\ApiSdk\Model\Funding;
use eLife\ApiSdk\Model\FundingAward;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class ArticleVoRNormalizer extends ArticleVersionNormalizer
{
    protected function denormalizeArticle(
        $data,
        PromiseInterface $article = null,
        string $class,
        string $format = null,
        array $context = []
    ) : ArticleVersion {
        if ($article) {
            $data['acknowledgements'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['acknowledgements'] ?? [];
                }));

            $data['additionalFiles'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['additionalFiles'] ?? [];
                }));

            $data['appendices'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['appendices'] ?? [];
                }));

            $data['authorResponse'] = $article
                ->then(function (Result $article) {
                    return $article['authorResponse'] ?? null;
                });

            $data['body'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['body'];
                }));

            $data['dataSets'] = $article
                ->then(function (Result $article) {
                    return $article['dataSets'] ?? null;
                });

            $data['decisionLetter'] = $article
                ->then(function (Result $article) {
                    return $article['decisionLetter'] ?? null;
                });

            $data['digest'] = $article
                ->then(function (Result $article) {
                    return $article['digest'] ?? null;
                });

            $data['ethics'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['ethics'] ?? [];
                }));

            $data['funding'] = $article
                ->then(function (Result $article) {
                    return $article['funding'] ?? null;
                });

            if (empty($data['image'])) {
                $data['image']['banner'] = promise_for(null);
            } else {
                $data['image']['banner'] = $article
                    ->then(function (Result $article) {
                        return $article['image']['banner'] ?? null;
                    });
            }

            $data['keywords'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['keywords'] ?? [];
                }));

            $data['references'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['references'] ?? [];
                }));
        } else {
            $data['acknowledgements'] = new ArraySequence($data['acknowledgements'] ?? []);

            $data['additionalFiles'] = new ArraySequence($data['additionalFiles'] ?? []);

            $data['appendices'] = new ArraySequence($data['appendices'] ?? []);

            $data['authorResponse'] = promise_for($data['authorResponse'] ?? null);

            $data['body'] = new ArraySequence($data['body']);

            $data['dataSets'] = promise_for($data['dataSets'] ?? null);

            $data['decisionLetter'] = promise_for($data['decisionLetter'] ?? null);

            $data['digest'] = promise_for($data['digest'] ?? null);

            $data['ethics'] = new ArraySequence($data['ethics'] ?? []);

            $data['funding'] = promise_for($data['funding'] ?? null);

            $data['image']['banner'] = promise_for($data['image']['banner'] ?? null);

            $data['keywords'] = new ArraySequence($data['keywords'] ?? []);

            $data['references'] = new ArraySequence($data['references'] ?? []);
        }

        $data['acknowledgements'] = $data['acknowledgements']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['additionalFiles'] = $data['additionalFiles']->map(function (array $file) use ($format, $context) {
            return $this->denormalizer->denormalize($file, File::class, $format, $context);
        });

        $data['appendices'] = $data['appendices']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Appendix::class, $format, $context);
        });

        $data['authorResponse'] = $data['authorResponse']
            ->then(function ($authorResponse) use ($format, $context) {
                if (empty($authorResponse)) {
                    return null;
                }

                return new ArticleSection(
                    new ArraySequence(array_map(function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    }, $authorResponse['content'])),
                    $authorResponse['doi']
                );
            });

        $data['body'] = $data['body']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $generatedDataSets = new PromiseSequence($data['dataSets']
            ->then(function (array $dataSets = null) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, DataSet::class, $format, $context);
                }, $dataSets['generated'] ?? []);
            }));

        $usedDataSets = new PromiseSequence($data['dataSets']
            ->then(function (array $dataSets = null) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, DataSet::class, $format, $context);
                }, $dataSets['used'] ?? []);
            }));

        $decisionLetterDescription = new PromiseSequence($data['decisionLetter']
            ->then(function (array $decisionLetter = null) use ($format, $context) {
                if (empty($decisionLetter)) {
                    return [];
                }

                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $decisionLetter['description']);
            }));

        $data['decisionLetter'] = $data['decisionLetter']
            ->then(function (array $decisionLetter = null) use ($format, $context) {
                if (empty($decisionLetter)) {
                    return null;
                }

                return new ArticleSection(
                    new ArraySequence(array_map(function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    }, $decisionLetter['content'])),
                    $decisionLetter['doi']
                );
            });

        $data['digest'] = $data['digest']
            ->then(function (array $digest = null) use ($format, $context) {
                if (empty($digest)) {
                    return null;
                }

                return new ArticleSection(
                    new ArraySequence(array_map(function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    }, $digest['content'])),
                    $digest['doi']
                );
            });

        $data['ethics'] = $data['ethics']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

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

        $data['image']['banner'] = $data['image']['banner']
            ->then(function (array $banner = null) use ($format, $context) {
                if (empty($banner)) {
                    return null;
                }

                return $this->denormalizer->denormalize($banner, Image::class,
                    $format, $context);
            });

        if (false === empty($data['image']['thumbnail'])) {
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class,
                $format, $context);
        }

        $data['references'] = $data['references']
            ->map(function (array $reference) use ($format, $context) {
                return $this->denormalizer->denormalize($reference, Reference::class, $format, $context);
            });

        return new ArticleVoR(
            $data['id'],
            $data['stage'],
            $data['version'],
            $data['type'],
            $data['doi'],
            $data['authorLine'],
            $data['titlePrefix'] ?? null,
            $data['title'],
            $data['published'],
            $data['versionDate'],
            $data['statusDate'],
            $data['volume'],
            $data['elocationId'],
            $data['pdf'] ?? null,
            $data['subjects'],
            $data['researchOrganisms'] ?? [],
            $data['abstract'],
            $data['issue'],
            $data['copyright'],
            $data['authors'],
            $data['reviewers'],
            $data['impactStatement'] ?? null,
            $data['image']['banner'],
            $data['image']['thumbnail'] ?? null,
            $data['keywords'],
            $data['digest'],
            $data['body'],
            $data['appendices'],
            $data['references'],
            $data['additionalFiles'],
            $generatedDataSets,
            $usedDataSets,
            $data['acknowledgements'],
            $data['ethics'],
            $data['funding'],
            $data['decisionLetter'],
            $decisionLetterDescription,
            $data['authorResponse'],
            $data['relatedArticles']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            ArticleVoR::class === $type
            ||
            (ArticleVersion::class === $type && 'vor' === $data['status'])
            ||
            is_a($type, Model::class, true) && $this->isArticleType($data['type'] ?? 'unknown') && 'vor' === ($data['status'] ?? 'unknown');
    }

    /**
     * @param ArticleVoR $article
     */
    protected function normalizeArticle(
        ArticleVersion $article,
        array $data,
        string $format = null,
        array $context = []
    ) : array {
        $data['status'] = 'vor';

        if ($article->getImpactStatement()) {
            $data['impactStatement'] = $article->getImpactStatement();
        }

        if ($article->getThumbnail()) {
            $data['image']['thumbnail'] = $this->normalizer->normalize($article->getThumbnail(), $format, $context);
        }

        if (empty($context['snippet'])) {
            if ($article->getBanner()) {
                $data['image']['banner'] = $this->normalizer->normalize($article->getBanner(), $format, $context);
            }

            if (count($article->getKeywords())) {
                $data['keywords'] = $article->getKeywords()->toArray();
            }

            if ($article->getDigest()) {
                $data['digest'] = [
                    'content' => $article->getDigest()->getContent()->map(function (Block $block) use (
                        $format,
                        $context
                    ) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray(),
                    'doi' => $article->getDigest()->getDoi(),
                ];
            }

            $data['body'] = $article->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();

            if (!$article->getAppendices()->isEmpty()) {
                $data['appendices'] = $article->getAppendices()
                    ->map(function (Appendix $appendix) use ($format, $context) {
                        return $this->normalizer->normalize($appendix, $format, $context);
                    })->toArray();
            }

            $data['references'] = $article->getReferences()->map(function (Reference $reference) use (
                $format,
                $context
            ) {
                return $this->normalizer->normalize($reference, $format, $context);
            })->toArray();

            if (empty($data['references'])) {
                unset($data['references']);
            }

            if ($article->getAdditionalFiles()->notEmpty()) {
                $data['additionalFiles'] = $article->getAdditionalFiles()
                    ->map(function (File $file) use ($format, $context) {
                        return $this->normalizer->normalize($file, $format, $context);
                    })->toArray();
            }

            if ($article->getGeneratedDataSets()->notEmpty()) {
                $data['dataSets']['generated'] = $article->getGeneratedDataSets()
                    ->map(function (DataSet $dataSet) use ($format, $context) {
                        return $this->normalizer->normalize($dataSet, $format, $context);
                    })->toArray();
            }

            if ($article->getUsedDataSets()->notEmpty()) {
                $data['dataSets']['used'] = $article->getUsedDataSets()
                    ->map(function (DataSet $dataSet) use ($format, $context) {
                        return $this->normalizer->normalize($dataSet, $format, $context);
                    })->toArray();
            }

            if (!$article->getAcknowledgements()->isEmpty()) {
                $data['acknowledgements'] = $article->getAcknowledgements()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if (!$article->getEthics()->isEmpty()) {
                $data['ethics'] = $article->getEthics()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if ($article->getFunding()) {
                if ($article->getFunding()->getAwards()->notEmpty()) {
                    $data['funding']['awards'] = $article->getFunding()->getAwards()
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
                $data['funding']['statement'] = $article->getFunding()->getStatement();
            }

            if ($article->getDecisionLetter()) {
                $data['decisionLetter'] = [
                    'description' => $article->getDecisionLetterDescription()
                        ->map(function (Block $block) use ($format, $context) {
                            return $this->normalizer->normalize($block, $format, $context);
                        })->toArray(),
                    'content' => $article->getDecisionLetter()->getContent()
                        ->map(function (Block $block) use (
                            $format,
                            $context
                        ) {
                            return $this->normalizer->normalize($block, $format, $context);
                        })->toArray(),
                    'doi' => $article->getDecisionLetter()->getDoi(),
                ];
            }

            if ($article->getAuthorResponse()) {
                $data['authorResponse'] = [
                    'content' => $article->getAuthorResponse()->getContent()
                        ->map(function (Block $block) use ($format, $context) {
                            return $this->normalizer->normalize($block, $format, $context);
                        })->toArray(),
                    'doi' => $article->getAuthorResponse()->getDoi(),
                ];
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ArticleVoR;
    }
}
