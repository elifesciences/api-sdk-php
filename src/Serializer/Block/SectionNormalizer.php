<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Section;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SectionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Section
    {
        $data['content'] = new ArraySequence(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['content']));

        return new Section($data['title'], $data['id'] ?? null, $data['content']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Section::class === $type
            ||
            (Block::class === $type && 'section' === $data['type']);
    }

    /**
     * @param Section $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'section',
            'title' => $object->getTitle(),
            'content' => $object->getContent()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray(),
        ];

        if ($object->getId()) {
            $data['id'] = $object->getId();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Section;
    }
}
