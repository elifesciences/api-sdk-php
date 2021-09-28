<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Reference;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;

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

            $data['editorEvaluation'] = $article
                ->then(function (Result $article) {
                    return $article['editorEvaluation'] ?? null;
                });

            $data['decisionLetter'] = $article
                ->then(function (Result $article) {
                    return $article['decisionLetter'] ?? null;
                });

            $data['digest'] = $article
                ->then(function (Result $article) {
                    return $article['digest'] ?? null;
                });

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

            $data['appendices'] = new ArraySequence($data['appendices'] ?? []);

            $data['authorResponse'] = promise_for($data['authorResponse'] ?? null);

            $data['body'] = new ArraySequence($data['body']);

            $data['editorEvaluation'] = promise_for($data['editorEvaluation'] ?? null);

            $data['decisionLetter'] = promise_for($data['decisionLetter'] ?? null);

            $data['digest'] = promise_for($data['digest'] ?? null);

            $data['keywords'] = new ArraySequence($data['keywords'] ?? []);

            $data['references'] = new ArraySequence($data['references'] ?? []);
        }

        $data['acknowledgements'] = $data['acknowledgements']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
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
                    $authorResponse['doi'] ?? null,
                    $authorResponse['id'] ?? null
                );
            });

        $data['body'] = $data['body']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $editorEvaluationScietyUri = $data['editorEvaluation']
            ->then(function (array $editorEvaluation = null) {
                if (empty($editorEvaluation)) {
                    return null;
                }

                return $editorEvaluation['uri'] ?? null;
            });

        $data['editorEvaluation'] = $data['editorEvaluation']
            ->then(function (array $editorEvaluation = null) use ($format, $context) {
                if (empty($editorEvaluation)) {
                    return null;
                }

                return new ArticleSection(
                    new ArraySequence(array_map(function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    }, $editorEvaluation['content'])),
                    $editorEvaluation['doi'] ?? null,
                    $editorEvaluation['id'] ?? null
                );
            });

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
                    $decisionLetter['doi'] ?? null,
                    $decisionLetter['id'] ?? null
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
                    $digest['doi'] ?? null
                );
            });

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
            $data['authorLine'] ?? null,
            $data['titlePrefix'] ?? null,
            $data['title'],
            $data['published'],
            $data['versionDate'],
            $data['statusDate'],
            $data['volume'],
            $data['elocationId'],
            $data['image']['thumbnail'] ?? null,
            $data['image']['social'] ?? null,
            $data['pdf'] ?? null,
            $data['figuresPdf'] ?? null,
            $data['xml'] ?? null,
            $data['subjects'],
            $data['researchOrganisms'] ?? [],
            $data['abstract'] ?? null,
            $data['issue'],
            $data['copyright'],
            $data['authors'],
            $data['reviewers'],
            $data['impactStatement'] ?? null,
            $data['keywords'],
            $data['digest'],
            $data['body'],
            $data['appendices'],
            $data['references'],
            $data['additionalFiles'],
            $data['dataAvailability'],
            $data['generatedDataSets'],
            $data['usedDataSets'],
            $data['acknowledgements'],
            $data['ethics'],
            $data['funding'],
            $data['editorEvaluation'],
            $editorEvaluationScietyUri,
            $data['decisionLetter'],
            $decisionLetterDescription,
            $data['authorResponse']
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

        if ($article->getFiguresPdf()) {
            $data['figuresPdf'] = $article->getFiguresPdf();
        }

        if ($article->getImpactStatement()) {
            $data['impactStatement'] = $article->getImpactStatement();
        }

        if (empty($context['snippet'])) {
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
                ];

                if ($article->getDigest()->getDoi()) {
                    $data['digest']['doi'] = $article->getDigest()->getDoi();
                }
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

            if (!$article->getAcknowledgements()->isEmpty()) {
                $data['acknowledgements'] = $article->getAcknowledgements()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if ($article->getEditorEvaluation()) {
                $data['editorEvaluation'] = [
                    'content' => $article->getEditorEvaluation()->getContent()
                        ->map(function (Block $block) use (
                            $format,
                            $context
                        ) {
                            return $this->normalizer->normalize($block, $format, $context);
                        })->toArray(),
                ];

                if ($article->getEditorEvaluationScietyUri()) {
                    $data['editorEvaluation']['uri'] = $article->getEditorEvaluationScietyUri();
                }

                if ($article->getEditorEvaluation()->getDoi()) {
                    $data['editorEvaluation']['doi'] = $article->getEditorEvaluation()->getDoi();
                }

                if ($article->getEditorEvaluation()->getId()) {
                    $data['editorEvaluation']['id'] = $article->getEditorEvaluation()->getId();
                }
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
                ];

                if ($article->getDecisionLetter()->getDoi()) {
                    $data['decisionLetter']['doi'] = $article->getDecisionLetter()->getDoi();
                }

                if ($article->getDecisionLetter()->getId()) {
                    $data['decisionLetter']['id'] = $article->getDecisionLetter()->getId();
                }
            }

            if ($article->getAuthorResponse()) {
                $data['authorResponse'] = [
                    'content' => $article->getAuthorResponse()->getContent()
                        ->map(function (Block $block) use ($format, $context) {
                            return $this->normalizer->normalize($block, $format, $context);
                        })->toArray(),
                ];

                if ($article->getAuthorResponse()->getDoi()) {
                    $data['authorResponse']['doi'] = $article->getAuthorResponse()->getDoi();
                }

                if ($article->getAuthorResponse()->getId()) {
                    $data['authorResponse']['id'] = $article->getAuthorResponse()->getId();
                }
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ArticleVoR;
    }
}
