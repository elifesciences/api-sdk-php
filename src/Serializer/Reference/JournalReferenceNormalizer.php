<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\ApiSdk\Model\Reference\ReferencePages;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class JournalReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : JournalReference
    {
        return new JournalReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors']),
            $data['authorsEtAl'] ?? false,
            $data['articleTitle'],
            $data['journal'],
            $this->denormalizer->denormalize($data['pages'], ReferencePages::class, $format, $context),
            $data['volume'] ?? null,
            $data['doi'] ?? null,
            $data['pmid'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            JournalReference::class === $type
            ||
            (Reference::class === $type && 'journal' === $data['type']);
    }

    /**
     * @param JournalReference $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'journal',
            'id' => $object->getId(),
            'date' => $object->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            }, $object->getAuthors()),
            'articleTitle' => $object->getArticleTitle(),
            'journal' => $object->getJournal(),
            'pages' => $this->normalizer->normalize($object->getPages(), $format, $context),
        ];

        if ($object->getDiscriminator()) {
            $data['discriminator'] = $object->getDiscriminator();
        }

        if ($object->authorsEtAl()) {
            $data['authorsEtAl'] = $object->authorsEtAl();
        }

        if ($object->getVolume()) {
            $data['volume'] = $object->getVolume();
        }

        if ($object->getDoi()) {
            $data['doi'] = $object->getDoi();
        }

        if ($object->getPmid()) {
            $data['pmid'] = $object->getPmid();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof JournalReference;
    }
}
