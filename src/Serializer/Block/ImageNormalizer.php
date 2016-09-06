<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Image;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ImageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Image
    {
        return new Image($data['uri'], $data['alt'], $data['caption'] ?? null);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Image::class === $type
            ||
            (Block::class === $type && 'image' === $data['type']);
    }

    /**
     * @param Image $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'image',
            'uri' => $object->getUri(),
            'alt' => $object->getAltText(),
        ];

        if ($object->getCaption()) {
            $data['caption'] = $object->getCaption();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Image;
    }
}
