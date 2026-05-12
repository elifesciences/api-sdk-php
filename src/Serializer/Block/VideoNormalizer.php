<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use eLife\ApiSdk\Model\Image;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class VideoNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Video
    {
        $data['placeholder'] = !empty($data['placeholder']) ? $this->denormalizer->denormalize($data['placeholder'], Image::class) : null;

        return new Video(
            $data['id'] ?? null,
            $data['title'] ?? null,
            new ArraySequence(array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? [])),
            new ArraySequence($data['attribution'] ?? []),
            array_map(function (array $source) {
                return new VideoSource($source['mediaType'], $source['uri']);
            }, $data['sources']), $data['placeholder'],
            $data['width'],
            $data['height'],
            $data['autoplay'] ?? false,
            $data['loop'] ?? false
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Video::class === $type
            ||
            (Block::class === $type && 'video' === $data['type']);
    }

    /**
     * @param Video $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'video',
            'sources' => array_map(function (VideoSource $source) {
                return [
                    'mediaType' => $source->getMediaType(),
                    'uri' => $source->getUri(),
                ];
            }, $data->getSources()),
            'width' => $data->getWidth(),
            'height' => $data->getHeight(),
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

        if ($data->getAttribution()->notEmpty()) {
            $arr['attribution'] = $data->getAttribution()->toArray();
        }

        if ($data->getPlaceholder()) {
            $arr['placeholder'] = $this->normalizer->normalize($data->getPlaceholder());
        }

        if ($data->isAutoplay()) {
            $arr['autoplay'] = $data->isAutoplay();
        }

        if ($data->isLoop()) {
            $arr['loop'] = $data->isLoop();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Video;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Video::class => false,
            Block::class => false,
        ];
    }
}
