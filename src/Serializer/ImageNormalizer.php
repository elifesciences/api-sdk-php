<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ImageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Image
    {
        $sizes = [];
        foreach ($data['sizes'] as $ratio => $images) {
            $sizes[] = new ImageSize($ratio, $images);
        }

        return new Image($data['alt'], $sizes);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Image::class === $type;
    }

    /**
     * @param Image $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'alt' => $object->getAltText(),
            'sizes' => [],
        ];

        foreach ($object->getSizes() as $size) {
            $data['sizes'][$size->getRatio()] = $size->getImages();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Image;
    }
}
