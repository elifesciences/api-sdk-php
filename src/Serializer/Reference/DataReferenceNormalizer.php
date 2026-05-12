<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\DataReference;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DataReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : DataReference
    {
        return new DataReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
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
            $data['specificUse'] ?? null,
            $data['doi'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            DataReference::class === $type
            ||
            (Reference::class === $type && 'data' === $data['type']);
    }

    /**
     * @param DataReference $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'data',
            'id' => $data->getId(),
            'date' => $data->getDate()->toString(),
            'title' => $data->getTitle(),
            'source' => $data->getSource(),
        ];

        if ($data->getDiscriminator()) {
            $arr['discriminator'] = $data->getDiscriminator();
        }

        if ($data->getAuthors()) {
            $arr['authors'] = array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, ['type' => true] + $context);
            }, $data->getAuthors());
        }

        if ($data->authorsEtAl()) {
            $arr['authorsEtAl'] = $data->authorsEtAl();
        }

        if ($data->getCompilers()) {
            $arr['compilers'] = array_map(function (AuthorEntry $compiler) use ($format, $context) {
                return $this->normalizer->normalize($compiler, $format, ['type' => true] + $context);
            }, $data->getCompilers());
        }

        if ($data->compilersEtAl()) {
            $arr['compilersEtAl'] = $data->compilersEtAl();
        }

        if ($data->getCurators()) {
            $arr['curators'] = array_map(function (AuthorEntry $curator) use ($format, $context) {
                return $this->normalizer->normalize($curator, $format, ['type' => true] + $context);
            }, $data->getCurators());
        }

        if ($data->curatorsEtAl()) {
            $arr['curatorsEtAl'] = $data->curatorsEtAl();
        }

        if ($data->getDataId()) {
            $arr['dataId'] = $data->getDataId();
        }

        if ($data->getAssigningAuthority()) {
            $arr['assigningAuthority'] = $this->normalizer->normalize($data->getAssigningAuthority(), $format,
                $context);
        }

        if ($data->getSpecificUse()) {
            $arr['specificUse'] = $data->getSpecificUse();
        }

        if ($data->getDoi()) {
            $arr['doi'] = $data->getDoi();
        }

        if ($data->getUri()) {
            $arr['uri'] = $data->getUri();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof DataReference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DataReference::class => false,
            Reference::class => false,
        ];
    }
}
