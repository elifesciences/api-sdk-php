<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\WebReference;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class WebReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : WebReference
    {
        return new WebReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors']),
            $data['authorsEtAl'] ?? false,
            $data['title'],
            $data['uri'],
            $data['website'] ?? null,
            !empty($data['accessed']) ? Date::fromString($data['accessed']) : null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            WebReference::class === $type
            ||
            (Reference::class === $type && 'web' === $data['type']);
    }

    /**
     * @param WebReference $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'web',
            'id' => $data->getId(),
            'date' => $data->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, ['type' => true] + $context);
            }, $data->getAuthors()),
            'title' => $data->getTitle(),
            'uri' => $data->getUri(),
        ];

        if ($data->getDiscriminator()) {
            $arr['discriminator'] = $data->getDiscriminator();
        }

        if ($data->authorsEtAl()) {
            $arr['authorsEtAl'] = $data->authorsEtAl();
        }

        if ($data->getWebsite()) {
            $arr['website'] = $data->getWebsite();
        }

        if ($data->getAccessed()) {
            $arr['accessed'] = $data->getAccessed()->toString();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof WebReference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            WebReference::class => false,
            Reference::class => false,
        ];
    }
}
