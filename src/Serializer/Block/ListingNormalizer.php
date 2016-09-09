<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Listing;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ListingNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Listing
    {
        return new Listing($data['ordered'] ?? false, array_map(function ($block) {
            if (is_string($block)) {
                return $block;
            }

            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['items']));
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
        $data = [
            'type' => 'list',
            'items' => array_map(function ($block) {
                if (is_string($block)) {
                    return $block;
                }

                return $this->normalizer->normalize($block);
            }, $object->getItems()),
        ];

        if ($object->isOrdered()) {
            $data['ordered'] = $object->isOrdered();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Listing;
    }
}
