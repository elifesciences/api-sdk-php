<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\File;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class VideoNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Video
    {
        return new Video($data['doi'] ?? null, $data['id'] ?? null, $data['label'] ?? null, $data['title'] ?? null,
            array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? []), array_map(function (array $source) {
                return new VideoSource($source['mediaType'], $source['uri']);
            }, $data['sources']), $data['image'] ?? null, $data['width'], $data['height'],
            array_map(function (array $file) {
                return $this->denormalizer->denormalize($file, File::class);
            }, $data['sourceData'] ?? []));
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

        if ($object->getDoi()) {
            $data['doi'] = $object->getDoi();
        }

        if ($object->getId()) {
            $data['id'] = $object->getId();
        }

        if ($object->getLabel()) {
            $data['label'] = $object->getLabel();
        }

        if ($object->getTitle()) {
            $data['title'] = $object->getTitle();
        }

        if ($object->getCaption()) {
            $data['caption'] = array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $object->getCaption());
        }

        if ($object->getImage()) {
            $data['image'] = $object->getImage();
        }

        if ($object->getSourceData()) {
            $data['sourceData'] = array_map(function (File $file) {
                return $this->normalizer->normalize($file);
            }, $object->getSourceData());
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Video;
    }
}
