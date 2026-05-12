<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figshare;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FigshareNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Figshare
    {
        return new Figshare(
            $data['id'],
            $data['title'],
            $data['width'],
            $data['height']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Figshare::class === $type
            ||
            (Block::class === $type && 'figshare' === $data['type']);
    }

    /**
     * @param Figshare $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'figshare',
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'width' => $data->getWidth(),
            'height' => $data->getHeight(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Figshare;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Figshare::class => false,
            Block::class => false,
        ];
    }
}
