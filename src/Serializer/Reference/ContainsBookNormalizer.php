<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ContainsBook;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;

/**
 * @internal
 */
trait ContainsBookNormalizer
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    final public function denormalize($data, $class, $format = null, array $context = []) : Reference
    {
        $data['date'] = Date::fromString($data['date']);
        $data['discriminator'] = $data['discriminator'] ?? null;
        $data['authors'] = array_map(function (array $author) {
            return $this->denormalizer->denormalize($author, AuthorEntry::class);
        }, $data['authors'] ?? []);
        $data['authorsEtAl'] = $data['authorsEtAl'] ?? false;
        $data['editors'] = array_map(function (array $editor) {
            return $this->denormalizer->denormalize($editor, AuthorEntry::class);
        }, $data['editors'] ?? []);
        $data['editorsEtAl'] = $data['editorsEtAl'] ?? false;
        $data['publisher'] = $this->denormalizer->denormalize($data['publisher'], Place::class, $format, $context);
        $data['volume'] = $data['volume'] ?? null;
        $data['edition'] = $data['edition'] ?? null;
        $data['doi'] = $data['doi'] ?? null;
        $data['pmid'] = $data['pmid'] ?? null;
        $data['isbn'] = $data['isbn'] ?? null;

        return $this->denormalizeReference($data, $class, $format, $context);
    }

    /**
     * @param Reference|ContainsBook $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'date' => $object->getDate()->toString(),
            'bookTitle' => $object->getBookTitle(),
            'publisher' => $this->normalizer->normalize($object->getPublisher(), $format, $context),
        ];

        if ($object->getDiscriminator()) {
            $data['discriminator'] = $object->getDiscriminator();
        }

        if ($object->getAuthors()) {
            $data['authors'] = array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, ['type' => true] + $context);
            }, $object->getAuthors());
        }

        if ($object->authorsEtAl()) {
            $data['authorsEtAl'] = $object->authorsEtAl();
        }

        if ($object->getEditors()) {
            $data['editors'] = array_map(function (AuthorEntry $editor) use ($format, $context) {
                return $this->normalizer->normalize($editor, $format, ['type' => true] + $context);
            }, $object->getEditors());
        }

        if ($object->editorsEtAl()) {
            $data['editorsEtAl'] = $object->editorsEtAl();
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

        return $this->normalizeReference($object, $data, $format, $context);
    }

    abstract protected function denormalizeReference($data, string $class, string $format = null, array $context = []) : Reference;

    abstract protected function normalizeReference(Reference $reference, array $data, string $format = null, array $context = []) : array;
}
