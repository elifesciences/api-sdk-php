<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class OnBehalfOfAuthorNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) :OnBehalfOfAuthor
    {
        return new OnBehalfOfAuthor($data['onBehalfOf']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            OnBehalfOfAuthor::class === $type
            ||
            (AuthorEntry::class === $type && 'on-behalf-of' === $data['type']);
    }

    /**
     * @param OnBehalfOfAuthor $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'type' => 'on-behalf-of',
            'onBehalfOf' => $object->getOnBehalfOf(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof OnBehalfOfAuthor;
    }
}
