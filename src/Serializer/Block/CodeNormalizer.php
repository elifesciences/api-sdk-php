<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Code;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CodeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : Code
    {
        return new Code($data['code'], $data['language'] ?? null);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Code::class === $type
            ||
            (Block::class === $type && 'code' === $data['type']);
    }

    /**
     * @param Code $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'code',
            'code' => $object->getCode(),
        ];

        if ($object->getLanguage()) {
            $data['language'] = $object->getLanguage();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Code;
    }
}
