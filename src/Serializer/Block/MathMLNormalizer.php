<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\MathML;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MathMLNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : MathML
    {
        return new MathML($data['id'] ?? null, $data['label'] ?? null, $data['mathml']);
    }

    public function supportsDenormalization($data, $type, $format = null)
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

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof MathML;
    }
}
