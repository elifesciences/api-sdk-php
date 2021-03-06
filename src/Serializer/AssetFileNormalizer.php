<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AssetFileNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : AssetFile
    {
        return new AssetFile($data['doi'] ?? null, $data['id'], $data['label'], $data['title'] ?? null,
            new ArraySequence(array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? [])), new ArraySequence($data['attribution'] ?? []), $this->denormalizer->denormalize($data, File::class, $format, $context));
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return AssetFile::class === $type;
    }

    /**
     * @param AssetFile $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = $this->normalizer->normalize($object->getFile());

        $data['id'] = $object->getId();
        $data['label'] = $object->getLabel();

        if ($object->getDoi()) {
            $data['doi'] = $object->getDoi();
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

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof AssetFile;
    }
}
