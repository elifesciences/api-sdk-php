<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Quote;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class QuoteNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Quote
    {
        return new Quote(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['text']), $data['cite'] ?? null);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Quote::class === $type
            ||
            (Block::class === $type && 'quote' === $data['type']);
    }

    /**
     * @param Quote $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'quote',
            'text' => array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $object->getText()),
        ];

        if ($object->getCite()) {
            $data['cite'] = $object->getCite();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Quote;
    }
}
