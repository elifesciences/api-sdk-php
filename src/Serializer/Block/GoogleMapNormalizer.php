<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\GoogleMap;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GoogleMapNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : GoogleMap
    {
        return new GoogleMap(
            $data['id'],
            $data['title']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            GoogleMap::class === $type
            ||
            (Block::class === $type && 'google-map' === $data['type']);
    }

    /**
     * @param GoogleMap $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'google-map',
            'id' => $data->getId(),
            'title' => $data->getTitle(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof GoogleMap;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            GoogleMap::class => false,
            Block::class => false,
        ];
    }
}
