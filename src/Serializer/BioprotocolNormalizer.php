<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Bioprotocol;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BioprotocolNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Bioprotocol
    {
        return new Bioprotocol($data['sectionId'], $data['status'], $data['uri']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Bioprotocol::class === $type;
    }

    /**
     * @param Bioprotocol $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'sectionId' => $object->getSectionId(),
            'status' => $object->getStatus(),
            'uri' => $object->getUri(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Bioprotocol;
    }
}
