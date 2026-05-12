<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\UnknownReference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class UnknownReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : UnknownReference
    {
        return new UnknownReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors']),
            $data['authorsEtAl'] ?? false,
            $data['title'],
            $data['details'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            UnknownReference::class === $type
            ||
            (Reference::class === $type && 'unknown' === $data['type']);
    }

    /**
     * @param UnknownReference $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'unknown',
            'id' => $data->getId(),
            'date' => $data->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, ['type' => true] + $context);
            }, $data->getAuthors()),
            'title' => $data->getTitle(),
        ];

        if ($data->getDiscriminator()) {
            $arr['discriminator'] = $data->getDiscriminator();
        }

        if ($data->authorsEtAl()) {
            $arr['authorsEtAl'] = $data->authorsEtAl();
        }

        if ($data->getDetails()) {
            $arr['details'] = $data->getDetails();
        }

        if ($data->getUri()) {
            $arr['uri'] = $data->getUri();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof UnknownReference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            UnknownReference::class => false,
            Reference::class => false,
        ];
    }
}
