<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class VideoNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Video
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

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Video::class === $type
            ||
            (Block::class === $type && 'video' === $data['type']);
    }

    /**
     * @param Video $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'video',
            'sources' => array_map(function (VideoSource $source) {
                return [
                    'mediaType' => $source->getMediaType(),
                    'uri' => $source->getUri(),
                ];
            }, $object->getSources()),
            'width' => $object->getWidth(),
            'height' => $object->getHeight(),
        ];

        if ($object->getId()) {
            $data['id'] = $object->getId();
        }

        if ($object->getTitle()) {
            $data['title'] = $object->getTitle();
        }

        if ($object->getCaption()->notEmpty()) {
            $data['caption'] = $object->getCaption()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray();
        }

        if ($object->getAttribution()->notEmpty()) {
            $data['attribution'] = $object->getAttribution()->toArray();
        }

        if ($object->getPlaceholder()) {
            $data['placeholder'] = $this->normalizer->normalize($object->getPlaceholder());
        }

        if ($object->isAutoplay()) {
            $data['autoplay'] = $object->isAutoplay();
        }

        if ($object->isLoop()) {
            $data['loop'] = $object->isLoop();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Video;
    }
}
