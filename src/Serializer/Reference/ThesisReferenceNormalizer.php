<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ThesisReference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ThesisReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : ThesisReference
    {
        return new ThesisReference(
            $this->denormalizer->denormalize($data['author'], Person::class),
            $data['title'],
            $this->denormalizer->denormalize($data['publisher'], Place::class, $format, $context),
            $data['doi'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            ThesisReference::class === $type
            ||
            (Reference::class === $type && 'thesis' === $data['type']);
    }

    /**
     * @param ThesisReference $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'thesis',
            'author' => $this->normalizer->normalize($object->getAuthor(), $format, $context),
            'title' => $object->getTitle(),
            'publisher' => $this->normalizer->normalize($object->getPublisher(), $format, $context),
        ];

        if ($object->getDoi()) {
            $data['doi'] = $object->getDoi();
        }

        if ($object->getUri()) {
            $data['uri'] = $object->getUri();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ThesisReference;
    }
}
