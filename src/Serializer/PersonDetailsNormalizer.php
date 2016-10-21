<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\PersonDetails;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PersonDetailsNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : PersonDetails
    {
        return new PersonDetails($data['name']['preferred'], $data['name']['index'], $data['orcid'] ?? null);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return PersonDetails::class === $type;
    }

    /**
     * @param PersonDetails $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'name' => [
                'preferred' => $object->getPreferredName(),
                'index' => $object->getIndexName(),
            ],
        ];

        if ($object->getOrcid()) {
            $data['orcid'] = $object->getOrcid();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PersonDetails;
    }
}
