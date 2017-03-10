<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlaceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Place
    {
        return new Place(
            $data['name'],
            !empty($data['address']) ? $this->denormalizer->denormalize($data['address'], Address::class, $format,
                $context) : null
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

        if ($object->getAddress()) {
            $data['address'] = $this->normalizer->normalize($object->getAddress(), $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Place;
    }
}
