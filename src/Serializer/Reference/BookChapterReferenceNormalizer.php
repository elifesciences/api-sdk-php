<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\ReferencePages;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BookChapterReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : BookChapterReference
    {
        return new BookChapterReference(
            ReferenceDate::fromString($data['date']),
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors']),
            $data['authorsEtAl'] ?? false,
            array_map(function (array $editor) {
                return $this->denormalizer->denormalize($editor, AuthorEntry::class);
            }, $data['editors']),
            $data['editorsEtAl'] ?? false,
            $data['chapterTitle'],
            $data['bookTitle'],
            $this->denormalizer->denormalize($data['publisher'], Place::class, $format, $context),
            $this->denormalizer->denormalize($data['pages'], ReferencePages::class, $format, $context),
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
            BookChapterReference::class === $type
            ||
            (Reference::class === $type && 'book-chapter' === $data['type']);
    }

    /**
     * @param BookChapterReference $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'book-chapter',
            'date' => $object->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            }, $object->getAuthors()),
            'editors' => array_map(function (AuthorEntry $editor) use ($format, $context) {
                return $this->normalizer->normalize($editor, $format, $context);
            }, $object->getEditors()),
            'chapterTitle' => $object->getChapterTitle(),
            'bookTitle' => $object->getBookTitle(),
            'publisher' => $this->normalizer->normalize($object->getPublisher(), $format, $context),
            'pages' => $this->normalizer->normalize($object->getPages(), $format, $context),
        ];

        if ($object->authorsEtAl()) {
            $data['authorsEtAl'] = $object->authorsEtAl();
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

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof BookChapterReference;
    }
}
