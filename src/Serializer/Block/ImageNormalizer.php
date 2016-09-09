<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\File;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Block\ImageFile;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ImageNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Image
    {
        $imageFiles = [$this->denormalizeImageFile($data)];
        foreach ($data['supplements'] ?? [] as $supplement) {
            $imageFiles[] = $this->denormalizeImageFile($supplement);
        }

        return new Image(...$imageFiles);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Image::class === $type
            ||
            (Block::class === $type && 'image' === $data['type']);
    }

    /**
     * @param Image $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = array_merge(['type' => 'image'], $this->normalizeImageFile($object->getImage()));

        if (false === empty($object->getSupplements())) {
            $data['supplements'] = array_map(function (ImageFile $supplement) {
                return $this->normalizeImageFile($supplement);
            }, $object->getSupplements());
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Image;
    }

    private function denormalizeImageFile(array $image) : ImageFile
    {
        return new ImageFile($image['doi'] ?? null, $image['id'] ?? null, $image['label'] ?? null,
            $image['title'] ?? null, array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $image['caption'] ?? []), $image['alt'], $image['uri'],
            $image['attribution'] ?? [], array_map(function (array $file) {
                return $this->denormalizer->denormalize($file, File::class);
            }, $image['sourceData'] ?? []));
    }

    private function normalizeImageFile(ImageFile $image) : array
    {
        $data = [
            'uri' => $image->getUri(),
            'alt' => $image->getAltText(),
        ];

        if ($image->getDoi()) {
            $data['doi'] = $image->getDoi();
        }

        if ($image->getId()) {
            $data['id'] = $image->getId();
        }

        if ($image->getLabel()) {
            $data['label'] = $image->getLabel();
        }

        if ($image->getTitle()) {
            $data['title'] = $image->getTitle();
        }

        if (count($image->getCaption())) {
            $data['caption'] = array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $image->getCaption());
        }

        if ($image->getAttribution()) {
            $data['attribution'] = $image->getAttribution();
        }

        if ($image->getSourceData()) {
            $data['sourceData'] = array_map(function (File $file) {
                return $this->normalizer->normalize($file);
            }, $image->getSourceData());
        }

        return $data;
    }
}
