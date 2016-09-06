<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ParagraphNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Paragraph
    {
        return new Paragraph($data['text']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Paragraph::class === $type
            ||
            (Block::class === $type && 'paragraph' === $data['type']);
    }

    /**
     * @param Paragraph $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'type' => 'paragraph',
            'text' => $object->getText(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Paragraph;
    }
}
