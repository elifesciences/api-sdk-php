<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use eLife\ApiSdk\Model\Reference\ReferencePages;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ConferenceProceedingReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : ConferenceProceedingReference
    {
        return new ConferenceProceedingReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors']),
            $data['authorsEtAl'] ?? false,
            $data['articleTitle'],
            $this->denormalizer->denormalize($data['conference'], Place::class, $format, $context),
            empty($data['pages']) ? null : $this->denormalizer->denormalize($data['pages'], ReferencePages::class,
                $format,
                $context),
            $data['doi'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            ConferenceProceedingReference::class === $type
            ||
            (Reference::class === $type && 'conference-proceeding' === $data['type']);
    }

    /**
     * @param ConferenceProceedingReference $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'conference-proceeding',
            'id' => $data->getId(),
            'date' => $data->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, ['type' => true] + $context);
            }, $data->getAuthors()),
            'articleTitle' => $data->getArticleTitle(),
            'conference' => $this->normalizer->normalize($data->getConference(), $format, $context),
        ];

        if ($data->getDiscriminator()) {
            $arr['discriminator'] = $data->getDiscriminator();
        }

        if ($data->authorsEtAl()) {
            $arr['authorsEtAl'] = $data->authorsEtAl();
        }

        if ($data->getPages()) {
            $arr['pages'] = $this->normalizer->normalize($data->getPages(), $format, $context);
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
        return $data instanceof ConferenceProceedingReference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ConferenceProceedingReference::class => false,
            Reference::class => false,
        ];
    }
}
