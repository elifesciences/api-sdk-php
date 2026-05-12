<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AnnotationDocument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnotationDocumentNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param $data
     * @param $type
     * @param $format
     * @param array $context
     * @return AnnotationDocument
     */
    public function denormalize($data, $type, $format = null, array $context = []) : AnnotationDocument
    {
        return new AnnotationDocument(
            $data['title'],
            $data['uri']);
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return AnnotationDocument::class === $type;
    }


    /**
     * @param AnnotationDocument $data
     * @param $format
     * @param array $context
     * @return array
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'title' => $data->getTitle(),
            'uri' => $data->getUri(),
        ];
    }

    /**
     * @param $data
     * @param $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof AnnotationDocument;
    }

    /**
     * @param string|null $format
     * @return true[]
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            AnnotationDocument::class => true,
        ];
    }
}
