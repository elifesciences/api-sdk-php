<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Profile;
use eLife\ApiSdk\Model\Image;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProfileNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Profile
    {
        $data['image'] = $this->denormalizer->denormalize($data['image'], Image::class);
        $data['content'] = new ArraySequence(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['content']));

        return new Profile($data['image'], $data['content']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Profile::class === $type
            ||
            (Block::class === $type && 'profile' === $data['type']);
    }

    /**
     * @param Profile $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'profile',
            'image' => $this->normalizer->normalize($data->getImage()),
            'content' => $data->getContent()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Profile;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Profile::class => false,
            Block::class => false,
        ];
    }
}
