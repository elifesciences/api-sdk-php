<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\PersonDetails;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PersonDetailsNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []) : PersonDetails
    {
        return new PersonDetails($data['name']['preferred'], $data['name']['index'], $data['orcid'] ?? null);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return PersonDetails::class === $type;
    }

    /**
     * @param PersonDetails $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'name' => [
                'preferred' => $data->getPreferredName(),
                'index' => $data->getIndexName(),
            ],
        ];

        if ($data->getOrcid()) {
            $arr['orcid'] = $data->getOrcid();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof PersonDetails;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PersonDetails::class => true,
        ];
    }
}
