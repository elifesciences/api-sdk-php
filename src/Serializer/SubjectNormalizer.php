<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Subject;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SubjectNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Subject
    {
        return new Subject(
            $data['id'],
            $data['name'],
            $data['impactStatement'] ?? null,
            $this->denormalizer->denormalize($data['image'], Image::class, $format, $context)
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Subject::class === $type;
    }

    /**
     * @param Subject $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'image' => $this->normalizer->normalize($object->getImage(), $format, $context),
        ];

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Subject;
    }
}
