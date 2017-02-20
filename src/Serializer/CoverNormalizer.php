<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CoverNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Cover
    {
        return new Cover(
            $data['title'],
            $this->denormalizer->denormalize($data['image'], Image::class, $format, $context),
            $this->denormalizer->denormalize($data['item'], Model::class, $format, ['snippet' => true])
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Cover::class === $type;
    }

    /**
     * @param Cover $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'title' => $object->getTitle(),
            'image' => $this->normalizer->normalize($object->getBanner()),
            'item' => $this->normalizer->normalize($object->getItem(), null, ['snippet' => true]),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Cover;
    }
}
