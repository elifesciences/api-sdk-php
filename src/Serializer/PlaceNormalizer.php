<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Coordinates;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlaceNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Place
    {
        return new Place(
            $data['id'] ?? null,
            !empty($data['coordinates']) ? new Coordinates($data['coordinates']['latitude'],
                $data['coordinates']['longitude']) : null,
            $data['name'],
            !empty($data['address']) ? new Address($data['address']['formatted'],
                $data['address']['components']['streetAddress'] ?? [],
                $data['address']['components']['locality'] ?? [], $data['address']['components']['area'] ?? [],
                $data['address']['components']['country'] ?? null,
                $data['address']['components']['postalCode'] ?? null) : null
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Place::class === $type;
    }

    /**
     * @param Place $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'name' => $object->getName(),
        ];

        if ($object->getId()) {
            $data['id'] = $object->getId();
        }

        if ($object->getCoordinates()) {
            $data['coordinates'] = [
                'latitude' => $object->getCoordinates()->getLatitude(),
                'longitude' => $object->getCoordinates()->getLongitude(),
            ];
        }

        if ($object->getAddress()) {
            $data['address'] = [
                'formatted' => $object->getAddress()->getFormatted(),
                'components' => [],
            ];

            if ($object->getAddress()->getStreetAddress()) {
                $data['address']['components']['streetAddress'] = $object->getAddress()->getStreetAddress();
            }

            if ($object->getAddress()->getLocality()) {
                $data['address']['components']['locality'] = $object->getAddress()->getLocality();
            }

            if ($object->getAddress()->getArea()) {
                $data['address']['components']['area'] = $object->getAddress()->getArea();
            }

            if ($object->getAddress()->getCountry()) {
                $data['address']['components']['country'] = $object->getAddress()->getCountry();
            }

            if ($object->getAddress()->getPostalCode()) {
                $data['address']['components']['postalCode'] = $object->getAddress()->getPostalCode();
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Place;
    }
}
