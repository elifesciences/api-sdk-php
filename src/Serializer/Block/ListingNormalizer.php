<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Listing;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ListingNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Listing
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

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Listing::class === $type
            ||
            (Block::class === $type && 'list' === $data['type']);
    }

    /**
     * @param Listing $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'type' => 'list',
            'prefix' => $object->getPrefix(),
            'items' => $object->getItems()->map(function ($item) {
                if ($item instanceof Sequence) {
                    return $item->map(function (Block $block) {
                        return $this->normalizer->normalize($block);
                    })->toArray();
                }

                return $this->normalizer->normalize($item);
            })->toArray(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Listing;
    }
}
