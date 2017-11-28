<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\AccessControl;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AccessControlNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : AccessControl
    {
        return new AccessControl(
            $data['value'],
            $data['access']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return AccessControl::class === $type;
    }

    /**
     * @param AccessControl $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'value' => $object->getValue(),
            'access' => $object->getAccess(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof AccessControl;
    }
}
