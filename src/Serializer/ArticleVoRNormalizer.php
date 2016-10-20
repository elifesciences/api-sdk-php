<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Reference;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class ArticleVoRNormalizer extends ArticleVersionNormalizer
{
    protected function denormalizeArticle(
        $data,
        PromiseInterface $article = null,
        $class,
        $format = null,
        array $context = []
    ) : ArticleVersion {
        if ($article) {
            $data['authorResponse'] = $article
                ->then(function (Result $article) {
                    return $article['authorResponse'] ?? null;
                });

            $data['body'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['body'];
                }));

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
            $data['authorResponse'] = promise_for($data['authorResponse'] ?? null);

            $data['body'] = new ArraySequence($data['body']);

            $data['decisionLetter'] = promise_for($data['decisionLetter'] ?? null);

            $data['digest'] = promise_for($data['digest'] ?? null);

            $data['keywords'] = new ArraySequence($data['keywords'] ?? []);

            $data['references'] = new ArraySequence($data['references'] ?? []);
        }

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

        if (false === empty($data['image'])) {
            $data['image'] = $this->denormalizer->denormalize($data['image'], Image::class, $format, $context);
        }

        $data['references'] = $data['references']
            ->map(function (array $reference) use ($format, $context) {
                return $this->denormalizer->denormalize($reference, Reference::class, $format, $context);
            });

        return new ArticleVoR(
            $data['id'],
            $data['version'],
            $data['type'],
            $data['doi'],
            $data['authorLine'],
            $data['titlePrefix'] ?? null,
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['statusDate']),
            $data['volume'],
            $data['elocationId'],
            $data['pdf'] ?? null,
            $data['subjects'],
            $data['researchOrganisms'] ?? [],
            $data['abstract'],
            $data['issue'],
            $data['copyright'],
            $data['authors'],
            $data['impactStatement'] ?? null,
            $data['image'] ?? null,
            $data['keywords'],
            $data['digest'],
            $data['body'],
            $data['references'],
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
            (ArticleVersion::class === $type && 'vor' === $data['status']);
    }

    /**
     * @param ArticleVoR $article
     */
    protected function normalizeArticle(
        ArticleVersion $article,
        array $data,
        $format = null,
        array $context = []
    ) : array {
        $data['status'] = 'vor';

        if ($article->getImpactStatement()) {
            $data['impactStatement'] = $article->getImpactStatement();
        }

        if ($article->getImage()) {
            $data['image'] = $this->normalizer->normalize($article->getImage(), $format, $context);
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
                    'doi' => $article->getDigest()->getDoi(),
                ];
            }

            $data['body'] = $article->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();

            $data['references'] = $article->getReferences()->map(function (Reference $reference) use (
                $format,
                $context
            ) {
                return $this->normalizer->normalize($reference, $format, $context);
            })->toArray();

            if (empty($data['references'])) {
                unset($data['references']);
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
