<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\MathML;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MathMLNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : MathML
    {
        return new MathML($data['id'] ?? null, $data['label'] ?? null, $data['mathml']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            MathML::class === $type
            ||
            (Block::class === $type && 'mathml' === $data['type']);
    }

    /**
     * @param MathML $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'mathml',
            'mathml' => $object->getMathML(),
        ];

        if ($object->getId()) {
            $data['id'] = $object->getId();
        }

        if ($object->getLabel()) {
            $data['label'] = $object->getLabel();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof MathML;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MathML::class => false,
            Block::class => false,
        ];
    }
}
