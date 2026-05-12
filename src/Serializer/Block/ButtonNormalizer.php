<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ButtonNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []) : Block\Button
    {
        return new Block\Button($data['text'], $data['uri']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Block\Button::class === $type
            ||
            (Block::class === $type && 'button' === $data['type']);
    }

    /**
     * @param Block\Button $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'button',
            'text' => $data->getText(),
            'uri' => $data->getUri(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Block\Button;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Block\Button::class => false,
            Block::class => false,
        ];
    }
}
