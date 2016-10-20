<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVersion;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticlePoANormalizer extends ArticleVersionNormalizer
{
    protected function denormalizeArticle(
        $data,
        PromiseInterface $article = null,
        $class,
        $format = null,
        array $context = []
    ) : ArticleVersion {
        return new ArticlePoA(
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
            $data['authors']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            ArticlePoA::class === $type
            ||
            (ArticleVersion::class === $type && 'poa' === $data['status']);
    }

    /**
     * @param ArticlePoA $article
     */
    protected function normalizeArticle(
        ArticleVersion $article,
        array $data,
        $format = null,
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
