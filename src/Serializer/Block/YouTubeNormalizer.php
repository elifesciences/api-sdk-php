<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\YouTube;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class YouTubeNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : YouTube
    {
        return new YouTube(
            $data['id'],
            $data['title'] ?? null,
            new ArraySequence(array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? [])),
            $data['width'],
            $data['height']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            YouTube::class === $type
            ||
            (Block::class === $type && 'youtube' === $data['type']);
    }

    /**
     * @param YouTube $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'youtube',
            'id' => $data->getId(),
            'width' => $data->getWidth(),
            'height' => $data->getHeight(),
        ];

        if ($data->getTitle()) {
            $arr['title'] = $data->getTitle();
        }

        if ($data->getCaption()->notEmpty()) {
            $arr['caption'] = $data->getCaption()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof YouTube;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            YouTube::class => false,
            Block::class => false,
        ];
    }
}
