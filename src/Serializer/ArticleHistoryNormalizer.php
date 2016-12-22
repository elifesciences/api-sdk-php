<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\ArticleHistory;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Date;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ArticleHistoryNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : ArticleHistory
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        return new ArticleHistory(
            isset($data['received']) ? Date::fromString($data['received']) : null,
            isset($data['accepted']) ? Date::fromString($data['accepted']) : null,
            $normalizationHelper->denormalizeArray($data['versions'], ArticleVersion::class, $context + ['snippet' => true])
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return ArticleHistory::class === $type;
    }

    /**
     * @param ArticleHistory $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [
            'versions' => $normalizationHelper->normalizeSequenceToSnippets($object->getVersions(), $context),
        ];

        if ($object->getReceived()) {
            $data['received'] = $object->getReceived()->toString();
        }

        if ($object->getAccepted()) {
            $data['accepted'] = $object->getAccepted()->toString();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ArticleHistory;
    }
}
