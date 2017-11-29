<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AccessControl;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AccessControlNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : AccessControl
    {
        if ($context['class'] ?? false) {
            $class = $context['class'];
            unset($context['class']);
            $data['value'] = $this->denormalizer->denormalize($data['value'], $class, $format, $context);
        }

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
            'value' => $this->normalizer->normalize($object->getValue(), $format, $context),
            'access' => $object->getAccess(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof AccessControl;
    }
}
