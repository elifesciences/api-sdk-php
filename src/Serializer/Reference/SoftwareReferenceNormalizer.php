<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\SoftwareReference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SoftwareReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : SoftwareReference
    {
        return new SoftwareReference(
            $data['id'],
            ReferenceDate::fromString($data['date']),
            $data['discriminator'] ?? null,
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors']),
            $data['authorsEtAl'] ?? false,
            $data['title'],
            $this->denormalizer->denormalize($data['publisher'], Place::class, $format, $context),
            $data['version'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            SoftwareReference::class === $type
            ||
            (Reference::class === $type && 'software' === $data['type']);
    }

    /**
     * @param SoftwareReference $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'software',
            'id' => $object->getId(),
            'date' => $object->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            }, $object->getAuthors()),
            'title' => $object->getTitle(),
            'publisher' => $this->normalizer->normalize($object->getPublisher(), $format, $context),
        ];

        if ($object->getDiscriminator()) {
            $data['discriminator'] = $object->getDiscriminator();
        }

        if ($object->authorsEtAl()) {
            $data['authorsEtAl'] = $object->authorsEtAl();
        }

        if ($object->getVersion()) {
            $data['version'] = $object->getVersion();
        }

        if ($object->getUri()) {
            $data['uri'] = $object->getUri();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof SoftwareReference;
    }
}
