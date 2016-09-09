<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\File;
use eLife\ApiSdk\Model\Block\Table;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TableNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Table
    {
        return new Table($data['doi'] ?? null, $data['id'] ?? null, $data['label'] ?? null,
            $data['title'] ?? null, array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? []), $data['tables'], array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['footer'] ?? []), array_map(function (array $file) {
                return $this->denormalizer->denormalize($file, File::class);
            }, $data['sourceData'] ?? []));
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Table::class === $type
            ||
            (Block::class === $type && 'table' === $data['type']);
    }

    /**
     * @param Table $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'table',
            'tables' => $object->getTables(),
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

        if (count($object->getCaption())) {
            $data['caption'] = array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $object->getCaption());
        }

        if (count($object->getFooter())) {
            $data['footer'] = array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $object->getFooter());
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
        return $data instanceof Table;
    }
}
