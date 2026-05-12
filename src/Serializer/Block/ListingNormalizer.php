<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Listing;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ListingNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Listing
    {
        return new Listing($data['prefix'], new ArraySequence(array_map(function ($item) {
            if (is_string($item)) {
                return $item;
            }

            return new ArraySequence(array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $item));
        }, $data['items'])));
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Listing::class === $type
            ||
            (Block::class === $type && 'list' === $data['type']);
    }

    /**
     * @param Listing $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'list',
            'prefix' => $data->getPrefix(),
            'items' => $data->getItems()->map(function ($item) {
                if ($item instanceof Sequence) {
                    return $item->map(function (Block $block) {
                        return $this->normalizer->normalize($block);
                    })->toArray();
                }

                return $this->normalizer->normalize($item);
            })->toArray(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Listing;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Listing::class => false,
            Block::class => false,
        ];
    }
}
