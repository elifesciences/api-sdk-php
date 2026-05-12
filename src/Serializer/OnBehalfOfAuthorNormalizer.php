<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class OnBehalfOfAuthorNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []) : OnBehalfOfAuthor
    {
        return new OnBehalfOfAuthor($data['onBehalfOf']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            OnBehalfOfAuthor::class === $type
            ||
            (AuthorEntry::class === $type && 'on-behalf-of' === $data['type']);
    }

    /**
     * @param OnBehalfOfAuthor $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'on-behalf-of',
            'onBehalfOf' => $data->toString(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof OnBehalfOfAuthor;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            OnBehalfOfAuthor::class => false,
            AuthorEntry::class => false,
        ];
    }
}
