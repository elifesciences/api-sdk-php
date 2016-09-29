<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Reference;
use function GuzzleHttp\Promise\promise_for;

final class ArticleVoRNormalizer extends ArticleVersionNormalizer
{
    public function denormalizeArticle($data, $class, $format = null, array $context = []) : ArticleVersion
    {
        $data['body'] = new PromiseCollection(promise_for($data['body'])
            ->then(function (array $blocks) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $blocks);
            }));

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
            $data['keywords'],
            $data['digest'],
            $data['body'],
            $data['references']
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
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ArticleVoR;
    }
}
