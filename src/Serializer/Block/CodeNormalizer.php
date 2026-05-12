<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Code;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CodeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []) : Code
    {
        return new Code($data['code'], $data['language'] ?? null);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Code::class === $type
            ||
            (Block::class === $type && 'code' === $data['type']);
    }

    /**
     * @param Code $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'code',
            'code' => $data->getCode(),
        ];

        if ($data->getLanguage()) {
            $arr['language'] = $data->getLanguage();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Code;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Code::class => false,
            Block::class => false,
        ];
    }
}
