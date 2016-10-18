<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Reference;
use function GuzzleHttp\Promise\promise_for;

final class ArticleVoRNormalizer extends ArticleVersionNormalizer
{
    public function denormalizeArticle($data, $class, $format = null, array $context = []) : ArticleVersion
    {
        if (empty($data['authorResponse'])) {
            $data['authorResponse'] = promise_for(null);
        } else {
            $data['authorResponse'] = promise_for($data['authorResponse'])
                ->then(function ($authorResponse) use ($format, $context) {
                    if (empty($authorResponse)) {
                        return null;
                    }

                    return new ArticleSection(
                        new ArrayCollection(array_map(function (array $block) use ($format, $context) {
                            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                        }, $authorResponse['content'])),
                        $authorResponse['doi']
                    );
                });
        }

        $data['body'] = new PromiseCollection(promise_for($data['body'])
            ->then(function (array $blocks) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $blocks);
            }));

        if (empty($data['decisionLetter'])) {
            $data['decisionLetter'] = promise_for(null);
            $decisionLetterDescription = new ArrayCollection([]);
        } else {
            $decisionLetterDescription = new PromiseCollection(promise_for($data['decisionLetter'])
                ->then(function (array $decisionLetter) use ($format, $context) {
                    return array_map(function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    }, $decisionLetter['description']);
                }));
            $data['decisionLetter'] = promise_for($data['decisionLetter'])
                ->then(function (array $decisionLetter) use ($format, $context) {
                    if (empty($decisionLetter)) {
                        return null;
                    }

                    return new ArticleSection(
                        new ArrayCollection(array_map(function (array $block) use ($format, $context) {
                            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                        }, $decisionLetter['content'])),
                        $decisionLetter['doi']
                    );
                });
        }

        if (empty($data['digest'])) {
            $data['digest'] = promise_for(null);
        } else {
            $data['digest'] = promise_for($data['digest'])
                ->then(function ($digest) use ($format, $context) {
                    if (empty($digest)) {
                        return null;
                    }

                    return new ArticleSection(
                        new ArrayCollection(array_map(function (array $block) use ($format, $context) {
                            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                        }, $digest['content'])),
                        $digest['doi']
                    );
                });
        }

        if (false === empty($data['image'])) {
            $data['image'] = $this->denormalizer->denormalize($data['image'], Image::class, $format, $context);
        }

        $data['keywords'] = new PromiseCollection(promise_for($data['keywords'] ?? []));

        $data['references'] = new PromiseCollection(promise_for($data['references'] ?? [])
            ->then(function (array $blocks) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Reference::class, $format, $context);
                }, $blocks);
            }));

        return new ArticleVoR(
            $data['id'],
            $data['version'],
            $data['type'],
            $data['doi'],
            $data['authorLine'],
            $data['titlePrefix'] ?? null,
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
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
