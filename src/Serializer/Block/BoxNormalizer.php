<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Box;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BoxNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Box
    {
        $data['content'] = new ArraySequence(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['content']));

        return new Box($data['doi'] ?? null, $data['id'] ?? null, $data['label'] ?? null, $data['title'], $data['content']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Box::class === $type
            ||
            (Block::class === $type && 'box' === $data['type']);
    }

    /**
     * @param Box $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'box',
            'title' => $object->getTitle(),
            'content' => $object->getContent()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray(),
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

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Box;
    }
}
