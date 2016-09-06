<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\YouTube;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class YouTubeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : YouTube
    {
        return new YouTube($data['id'], $data['width'], $data['height']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            YouTube::class === $type
            ||
            (Block::class === $type && 'youtube' === $data['type']);
    }

    /**
     * @param YouTube $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'type' => 'youtube',
            'id' => $object->getId(),
            'width' => $object->getWidth(),
            'height' => $object->getHeight(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof YouTube;
    }
}
