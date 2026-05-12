<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class PlaceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Place
    {
        return new Place(
            $data['name'],
            !empty($data['address']) ? $this->denormalizer->denormalize($data['address'], Address::class, $format,
                $context) : null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Place::class === $type;
    }

    /**
     * @param Place $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'name' => $data->getName(),
        ];

        if ($data->getAddress()) {
            $arr['address'] = $this->normalizer->normalize($data->getAddress(), $format, $context);
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Place;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Place::class => true,
        ];
    }
}
