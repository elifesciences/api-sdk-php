<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AnnotationDocument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnotationDocumentNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : AnnotationDocument
    {
        return new AnnotationDocument(
            $data['title'],
            $data['uri']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return AnnotationDocument::class === $type;
    }

    /**
     * @param AnnotationDocument $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'title' => $object->getTitle(),
            'uri' => $object->getUri(),
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof AnnotationDocument;
    }
}
