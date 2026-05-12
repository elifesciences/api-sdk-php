<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\File;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FileNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []) : File
    {
        return new File($data['mediaType'], $data['uri'], $data['filename']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return File::class === $type;
    }

    /**
     * @param File $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'mediaType' => $data->getMediaType(),
            'uri' => $data->getUri(),
            'filename' => $data->getFilename(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof File;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            File::class => true,
        ];
    }
}
