<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BookReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : BookReference
    {
        return new BookReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors']),
            $data['authorsEtAl'] ?? false,
            $data['authorsType'] ?? 'authors',
            $data['bookTitle'],
            $this->denormalizer->denormalize($data['publisher'], Place::class, $format, $context),
            $data['volume'] ?? null,
            $data['edition'] ?? null,
            $data['doi'] ?? null,
            $data['pmid'] ?? null,
            $data['isbn'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            BookReference::class === $type
            ||
            (Reference::class === $type && 'book' === $data['type']);
    }

    /**
     * @param BookReference $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'book',
            'id' => $object->getId(),
            'date' => $object->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            }, $object->getAuthors()),
            'bookTitle' => $object->getBookTitle(),
            'publisher' => $this->normalizer->normalize($object->getPublisher(), $format, $context),
        ];

        if ($object->getDiscriminator()) {
            $data['discriminator'] = $object->getDiscriminator();
        }

        if ($object->authorsEtAl()) {
            $data['authorsEtAl'] = $object->authorsEtAl();
        }

        if ('authors' !== $object->getAuthorsType()) {
            $data['authorsType'] = $object->getAuthorsType();
        }

        if ($object->getVolume()) {
            $data['volume'] = $object->getVolume();
        }

        if ($object->getEdition()) {
            $data['edition'] = $object->getEdition();
        }

        if ($object->getDoi()) {
            $data['doi'] = $object->getDoi();
        }

        if ($object->getPmid()) {
            $data['pmid'] = $object->getPmid();
        }

        if ($object->getIsbn()) {
            $data['isbn'] = $object->getIsbn();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof BookReference;
    }
}
