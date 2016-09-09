<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Person;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PersonNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Person
    {
        return new Person($data['name']['preferred'], $data['name']['index'], $data['orcid'] ?? null);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Person::class === $type;
    }

    /**
     * @param Person $object
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
        return $data instanceof Person;
    }
}
