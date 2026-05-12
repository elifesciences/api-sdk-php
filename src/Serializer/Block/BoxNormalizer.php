<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Box;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BoxNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Box
    {
        $data['content'] = new ArraySequence(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['content']));

        return new Box($data['doi'] ?? null, $data['id'] ?? null, $data['label'] ?? null, $data['title'], $data['content']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Box::class === $type
            ||
            (Block::class === $type && 'box' === $data['type']);
    }

    /**
     * @param Box $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'box',
            'title' => $data->getTitle(),
            'content' => $data->getContent()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray(),
        ];

        if ($data->getDoi()) {
            $arr['doi'] = $data->getDoi();
        }

        if ($data->getId()) {
            $arr['id'] = $data->getId();
        }

        if ($data->getLabel()) {
            $arr['label'] = $data->getLabel();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Box;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Box::class => false,
            Block::class => false,
        ];
    }
}
