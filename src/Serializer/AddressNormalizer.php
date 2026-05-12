<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Address;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AddressNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * @param $data
     * @param $type
     * @param $format
     * @param array $context
     * @return Address
     */
    public function denormalize($data, $type, $format = null, array $context = []) : Address
    {
        return new Address(
            new ArraySequence($data['formatted']),
            new ArraySequence($data['components']['streetAddress'] ?? []),
            new ArraySequence($data['components']['locality'] ?? []),
            new ArraySequence($data['components']['area'] ?? []),
            $data['components']['country'] ?? null,
            $data['components']['postalCode'] ?? null);
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
        return Address::class === $type;
    }

    /**
     * @param Address $data
     * @param $format
     * @param array $context
     * @return array
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'formatted' => $data->getFormatted()->toArray(),
            'components' => [],
        ];

        if ($data->getStreetAddress()->notEmpty()) {
            $arr['components']['streetAddress'] = $data->getStreetAddress()->toArray();
        }

        if ($data->getLocality()->notEmpty()) {
            $arr['components']['locality'] = $data->getLocality()->toArray();
        }

        if ($data->getArea()->notEmpty()) {
            $arr['components']['area'] = $data->getArea()->toArray();
        }

        if ($data->getCountry()) {
            $arr['components']['country'] = $data->getCountry();
        }

        if ($data->getPostalCode()) {
            $arr['components']['postalCode'] = $data->getPostalCode();
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
        return $data instanceof Address;
    }

    /**
     * @param string|null $format
     * @return true[]
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Address::class => true,
        ];
    }
}
