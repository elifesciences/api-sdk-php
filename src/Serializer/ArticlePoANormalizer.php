<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticlePoANormalizer extends ArticleVersionNormalizer
{
    protected function denormalizeArticle(
        $data,
        PromiseInterface $article = null,
        string $class,
        string $format = null,
        array $context = []
    ) : ArticleVersion {
        return new ArticlePoA(
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
            $data['relatedArticles'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            ArticlePoA::class === $type
            ||
            (ArticleVersion::class === $type && 'poa' === $data['status'])
            ||
            Model::class === $type && $this->isArticleType($data['type'] ?? 'unknown') && 'poa' === ($data['status'] ?? 'unknown');
    }

    /**
     * @param ArticlePoA $article
     */
    protected function normalizeArticle(
        ArticleVersion $article,
        array $data,
        string $format = null,
        array $context = []
    ) : array {
        $data['status'] = 'poa';

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ArticlePoA;
    }
}
