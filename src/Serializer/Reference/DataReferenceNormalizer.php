<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\DataReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DataReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : DataReference
    {
        return new DataReference(
            ReferenceDate::fromString($data['date']),
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors'] ?? []),
            $data['authorsEtAl'] ?? false,
            array_map(function (array $compiler) {
                return $this->denormalizer->denormalize($compiler, AuthorEntry::class);
            }, $data['compilers'] ?? []),
            $data['curatorsEtAl'] ?? false,
            array_map(function (array $curators) {
                return $this->denormalizer->denormalize($curators, AuthorEntry::class);
            }, $data['curators'] ?? []),
            $data['authorsEtAl'] ?? false,
            $data['title'],
            $data['source'],
            $data['dataId'] ?? null,
            empty($data['assigningAuthority']) ? null : $this->denormalizer->denormalize($data['assigningAuthority'],
                Place::class, $format, $context),
            $data['doi'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            DataReference::class === $type
            ||
            (Reference::class === $type && 'data' === $data['type']);
    }

    /**
     * @param DataReference $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'data',
            'date' => $object->getDate()->toString(),
            'title' => $object->getTitle(),
            'source' => $object->getSource(),
        ];

        if ($object->getAuthors()) {
            $data['authors'] = array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            }, $object->getAuthors());
        }

        if ($object->authorsEtAl()) {
            $data['authorsEtAl'] = $object->authorsEtAl();
        }

        if ($object->getCompilers()) {
            $data['compilers'] = array_map(function (AuthorEntry $compiler) use ($format, $context) {
                return $this->normalizer->normalize($compiler, $format, $context);
            }, $object->getCompilers());
        }

        if ($object->compilersEtAl()) {
            $data['compilersEtAl'] = $object->compilersEtAl();
        }

        if ($object->getCurators()) {
            $data['curators'] = array_map(function (AuthorEntry $curator) use ($format, $context) {
                return $this->normalizer->normalize($curator, $format, $context);
            }, $object->getCurators());
        }

        if ($object->curatorsEtAl()) {
            $data['curatorsEtAl'] = $object->curatorsEtAl();
        }

        if ($object->getDataId()) {
            $data['dataId'] = $object->getDataId();
        }

        if ($object->getAssigningAuthority()) {
            $data['assigningAuthority'] = $this->normalizer->normalize($object->getAssigningAuthority(), $format,
                $context);
        }

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
        return $data instanceof DataReference;
    }
}
