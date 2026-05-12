<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use eLife\ApiSdk\Model\Reference\ReferencePages;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ReferencePagesNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []) : ReferencePages
    {
        if (is_string($data)) {
            return new StringReferencePage($data);
        }

        return new ReferencePageRange($data['first'], $data['last'], $data['range']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return ReferencePages::class === $type;
    }

    /**
     * @param ReferencePages $data
     */
    public function normalize($data, $format = null, array $context = []): array | string
    {
        if ($data instanceof ReferencePageRange) {
            return [
                'first' => $data->getFirst(),
                'last' => $data->getLast(),
                'range' => $data->getRange(),
            ];
        }

        return $data->toString();
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof ReferencePages;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ReferencePages::class => false,
        ];
    }
}
