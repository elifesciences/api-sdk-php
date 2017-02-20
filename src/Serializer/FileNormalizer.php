<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FileNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : File
    {
        return new File($data['doi'] ?? null, $data['id'] ?? null, $data['label'] ?? null, $data['title'] ?? null,
            new ArraySequence(array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? [])), $data['mediaType'], $data['uri'], $data['filename']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return File::class === $type;
    }

    /**
     * @param File $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'mediaType' => $object->getMediaType(),
            'uri' => $object->getUri(),
            'filename' => $object->getFilename(),
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

        if ($object->getCaption()->notEmpty()) {
            $data['caption'] = $object->getCaption()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof File;
    }
}
