<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticlePoANormalizer extends ArticleVersionNormalizer
{
    /**
     * @param $data
     * @param PromiseInterface|null $article
     * @param string $class
     * @param string|null $format
     * @param array $context
     * @return ArticleVersion
     */
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
            $data['xml'] ?? null,
            $data['subjects'],
            $data['researchOrganisms'] ?? [],
            $data['abstract'] ?? null,
            $data['issue'],
            $data['copyright'],
            $data['authors'],
            $data['reviewers'],
            $data['ethics'],
            $data['funding'],
            $data['dataAvailability'],
            $data['generatedDataSets'],
            $data['usedDataSets'],
            $data['additionalFiles']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            ArticlePoA::class === $type
            ||
            (ArticleVersion::class === $type && 'poa' === $data['status'])
            ||
            is_a($type, Model::class, true) && $this->isArticleType($data['type'] ?? 'unknown') && 'poa' === ($data['status'] ?? 'unknown');
    }

    /**
     * @param ArticlePoA $article
     * @param array $data
     * @param string|null $format
     * @param array $context
     * @return array
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

    /**
     * @param $data
     * @param $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof ArticlePoA;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ArticlePoA::class => false,
            ArticleVersion::class => false,
            Model::class => false,
        ];
    }
}
