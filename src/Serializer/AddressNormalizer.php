<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Address;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AddressNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Address
    {
        return new Address(
            new ArraySequence($data['formatted']),
            new ArraySequence($data['components']['streetAddress'] ?? []),
            new ArraySequence($data['components']['locality'] ?? []),
            new ArraySequence($data['components']['area'] ?? []),
            $data['components']['country'] ?? null,
            $data['components']['postalCode'] ?? null);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Address::class === $type;
    }

    /**
     * @param Address $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'formatted' => $object->getFormatted()->toArray(),
            'components' => [],
        ];

        if ($object->getStreetAddress()->notEmpty()) {
            $data['components']['streetAddress'] = $object->getStreetAddress()->toArray();
        }

        if ($object->getLocality()->notEmpty()) {
            $data['components']['locality'] = $object->getLocality()->toArray();
        }

        if ($object->getArea()->notEmpty()) {
            $data['components']['area'] = $object->getArea()->toArray();
        }

        if ($object->getCountry()) {
            $data['components']['country'] = $object->getCountry();
        }

        if ($object->getPostalCode()) {
            $data['components']['postalCode'] = $object->getPostalCode();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Address;
    }
}
