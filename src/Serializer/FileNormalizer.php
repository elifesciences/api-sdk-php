<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\File;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FileNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : File
    {
        return new File($data['mediaType'], $data['uri'], $data['filename']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return File::class === $type;
    }

    /**
     * @param File $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'mediaType' => $object->getMediaType(),
            'uri' => $object->getUri(),
            'filename' => $object->getFilename(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof File;
    }
}
