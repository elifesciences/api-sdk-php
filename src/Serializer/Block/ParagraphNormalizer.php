<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ParagraphNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []) : Paragraph
    {
        return new Paragraph($data['text']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Paragraph::class === $type
            ||
            (Block::class === $type && 'paragraph' === $data['type']);
    }

    /**
     * @param Paragraph $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'paragraph',
            'text' => $data->getText(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Paragraph;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Paragraph::class => false,
            Block::class => false,
        ];
    }
}
