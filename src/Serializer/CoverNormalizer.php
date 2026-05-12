<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class CoverNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Cover
    {
        return new Cover(
            $data['title'],
            $data['impactStatement'] ?? null,
            $this->denormalizer->denormalize($data['image'], Image::class, $format, $context),
            $this->denormalizer->denormalize($data['item'], Model::class, $format, ['snippet' => true])
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Cover::class === $type;
    }


    /**
     * @param Cover $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'title' => $data->getTitle(),
            'image' => $this->normalizer->normalize($data->getBanner()),
            'item' => $this->normalizer->normalize($data->getItem(), null, ['type' => true, 'snippet' => true]),
        ];

        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Cover;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Cover::class => true,
        ];
    }
}
