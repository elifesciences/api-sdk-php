<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Image;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function var_dump;

final class ImageNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Image
    {
        $data['source'] = $this->denormalizer->denormalize($data['source'], File::class);

        return new Image($data['alt'], $data['uri'], new ArraySequence($data['attribution'] ?? []), $data['source'], $data['size']['width'], $data['size']['height'], $data['focalPoint']['x'] ?? 50, $data['focalPoint']['y'] ?? 50);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Image::class === $type;
    }

    /**
     * @param Image $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'alt' => $object->getAltText(),
            'uri' => $object->getUri(),
            'source' => $this->normalizer->normalize($object->getSource()),
            'size' => [
                'width' => $object->getWidth(),
                'height' => $object->getHeight(),
            ],
            'focalPoint' => [
                'x' => $object->getFocalPointX(),
                'y' => $object->getFocalPointY(),
            ],
        ];

        if (50 === $data['focalPoint']['x'] && 50 === $data['focalPoint']['y']) {
            unset($data['focalPoint']);
        }

        if ($object->getAttribution()->notEmpty()) {
            $data['attribution'] = $object->getAttribution()->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Image;
    }
}
