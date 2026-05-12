<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\ArticleHistory;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Date;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ArticleHistoryNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    /**
     * @param $data
     * @param $type
     * @param $format
     * @param array $context
     * @return ArticleHistory
     */
    public function denormalize($data, $type, $format = null, array $context = []) : ArticleHistory
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        return new ArticleHistory(
            isset($data['received']) ? Date::fromString($data['received']) : null,
            isset($data['accepted']) ? Date::fromString($data['accepted']) : null,
            isset($data['sentForReview']) ? Date::fromString($data['sentForReview']) : null,
            $normalizationHelper->denormalizeArray($data['versions'], ArticleVersion::class, $context + ['snippet' => true])
        );
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return ArticleHistory::class === $type;
    }


    /**
     * @param ArticleHistory $data
     * @param $format
     * @param array $context
     * @return array
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $arr = [
            'versions' => $normalizationHelper->normalizeSequenceToSnippets($data->getVersions(), $context),
        ];

        if ($data->getReceived()) {
            $arr['received'] = $data->getReceived()->toString();
        }

        if ($data->getAccepted()) {
            $arr['accepted'] = $data->getAccepted()->toString();
        }

        if ($data->getSentForReview()) {
            $arr['sentForReview'] = $data->getSentForReview()->toString();
        }

        return $arr;
    }

    /**
     * @param $data
     * @param $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof ArticleHistory;
    }

    /**
     * @param string|null $format
     * @return array
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            ArticleHistory::class => true,
        ];
    }
}
