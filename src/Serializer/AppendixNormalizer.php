<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\Block;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AppendixNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Appendix
    {
        return new Appendix($data['id'], $data['title'], new ArraySequence(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['content'])), $data['doi']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return Appendix::class === $type;
    }

    /**
     * @param Appendix $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'content' => $object->getContent()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray(),
            'doi' => $object->getDoi(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Appendix;
    }
}
