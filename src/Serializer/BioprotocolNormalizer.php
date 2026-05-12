<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Bioprotocol;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BioprotocolNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []) : Bioprotocol
    {
        return new Bioprotocol($data['sectionId'], $data['title'], $data['status'], $data['uri']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Bioprotocol::class === $type;
    }

    /**
     * @param Bioprotocol $data
     * @param $format
     * @param array $context
     * @return array
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'sectionId' => $data->getSectionId(),
            'title' => $data->getTitle(),
            'status' => $data->getStatus(),
            'uri' => $data->getUri(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Bioprotocol;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Bioprotocol::class => true,
        ];
    }
}
