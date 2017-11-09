<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Profile;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProfileNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Profile
    {
        $data['image'] = $this->denormalizer->denormalize($data['image'], Image::class);
        $data['content'] = new ArraySequence(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['content']));

        return new Profile($data['image'], $data['content']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Profile::class === $type
            ||
            (Block::class === $type && 'profile' === $data['type']);
    }

    /**
     * @param Profile $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'type' => 'profile',
            'image' => $this->normalizer->normalize($object->getImage()),
            'content' => $object->getContent()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Profile;
    }
}
