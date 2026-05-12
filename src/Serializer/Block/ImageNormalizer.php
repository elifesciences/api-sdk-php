<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Image as ImageFile;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ImageNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Image
    {
        return new Image(
            $data['id'] ?? null,
            $data['title'] ?? null,
            new ArraySequence(array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? [])),
            $this->denormalizer->denormalize($data['image'], ImageFile::class),
            $data['inline'] ?? false
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Image::class === $type
            ||
            (Block::class === $type && 'image' === $data['type']);
    }

    /**
     * @param Image $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'image',
            'image' => $this->normalizer->normalize($data->getImage()),
        ];

        if ($data->getId()) {
            $arr['id'] = $data->getId();
        }

        if ($data->getTitle()) {
            $arr['title'] = $data->getTitle();
        }

        if ($data->getCaption()->notEmpty()) {
            $arr['caption'] = $data->getCaption()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray();
        }

        if ($data->isInline()) {
            $arr['inline'] = true;
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Image;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Image::class => false,
            Block::class => false,
        ];
    }
}
