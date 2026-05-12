<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Quote;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class QuoteNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Quote
    {
        return new Quote(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['text']), $data['cite'] ?? null);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Quote::class === $type
            ||
            (Block::class === $type && 'quote' === $data['type']);
    }

    /**
     * @param Quote $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'quote',
            'text' => array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $data->getText()),
        ];

        if ($data->getCite()) {
            $arr['cite'] = $data->getCite();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Quote;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Quote::class => false,
            Block::class => false,
        ];
    }
}
