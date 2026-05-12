<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ThesisReference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ThesisReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : ThesisReference
    {
        return new ThesisReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
            $this->denormalizer->denormalize($data['author'], PersonDetails::class),
            $data['title'],
            $this->denormalizer->denormalize($data['publisher'], Place::class, $format, $context),
            $data['doi'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            ThesisReference::class === $type
            ||
            (Reference::class === $type && 'thesis' === $data['type']);
    }

    /**
     * @param ThesisReference $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'thesis',
            'id' => $data->getId(),
            'date' => $data->getDate()->toString(),
            'author' => $this->normalizer->normalize($data->getAuthor(), $format, ['type' => true] + $context),
            'title' => $data->getTitle(),
            'publisher' => $this->normalizer->normalize($data->getPublisher(), $format, $context),
        ];

        if ($data->getDiscriminator()) {
            $arr['discriminator'] = $data->getDiscriminator();
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
        return $data instanceof ThesisReference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ThesisReference::class => false,
            Reference::class => false,
        ];
    }
}
