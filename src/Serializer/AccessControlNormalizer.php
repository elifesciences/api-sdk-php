<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AccessControl;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class AccessControlNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    /**
     * @param $data
     * @param $type
     * @param $format
     * @param array $context
     * @return AccessControl
     * @throws ExceptionInterface
     */
    public function denormalize($data, $type, $format = null, array $context = []) : AccessControl
    {
        if ($context['class'] ?? false) {
            $type = $context['class'];
            unset($context['class']);
            $data['value'] = $this->denormalizer->denormalize($data['value'], $type, $format, $context);
        }

        return new AccessControl(
            $data['value'],
            $data['access']
        );
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return AccessControl::class === $type;
    }

    /**
     * @param AccessControl $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'value' => $this->normalizer->normalize($data->getValue(), $format, $context),
            'access' => $data->getAccess(),
        ];
    }

    /**
     * @param $data
     * @param $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof AccessControl;
    }

    /**
     * @param string|null $format
     * @return true[]
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            AccessControl::class => true,
        ];
    }
}
