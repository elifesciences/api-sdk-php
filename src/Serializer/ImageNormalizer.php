<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Image;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ImageNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Image
    {
        $data['source'] = $this->denormalizer->denormalize($data['source'], File::class);

        return new Image($data['alt'], $data['uri'], new ArraySequence($data['attribution'] ?? []), $data['source'], $data['size']['width'], $data['size']['height'], $data['focalPoint']['x'] ?? 50, $data['focalPoint']['y'] ?? 50);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Image::class === $type;
    }

    /**
     * @param Image $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'alt' => $data->getAltText(),
            'uri' => $data->getUri(),
            'source' => $this->normalizer->normalize($data->getSource()),
            'size' => [
                'width' => $data->getWidth(),
                'height' => $data->getHeight(),
            ],
            'focalPoint' => [
                'x' => $data->getFocalPointX(),
                'y' => $data->getFocalPointY(),
            ],
        ];

        if (50 === $arr['focalPoint']['x'] && 50 === $arr['focalPoint']['y']) {
            unset($arr['focalPoint']);
        }

        if ($data->getAttribution()->notEmpty()) {
            $arr['attribution'] = $data->getAttribution()->toArray();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Image;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Image::class => true,
        ];
    }
}
