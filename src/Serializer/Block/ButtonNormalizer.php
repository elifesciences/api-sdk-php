<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ButtonNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Block\Button
    {
        return new Block\Button($data['text'], $data['uri']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Block\Button::class === $type
            ||
            (Block::class === $type && 'button' === $data['type']);
    }

    /**
     * @param Block\Button $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'type' => 'button',
            'text' => $object->getText(),
            'uri' => $object->getUri(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Block\Button;
    }
}
