<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Annotation;
use eLife\ApiSdk\Model\AnnotationDocument;
use eLife\ApiSdk\Model\Block;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnotationNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Annotation
    {
        $data['content'] = new ArraySequence($data['content'] ?? []);

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['document'] = $this->denormalizer->denormalize($data['document'], AnnotationDocument::class, $format, $context);

        return new Annotation(
            $data['id'],
            $data['access'],
            $data['document'],
            new ArraySequence($data['parents'] ?? []),
            $data['highlight'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['created']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['content']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Annotation::class === $type;
    }

    /**
     * @param Annotation $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'access' => $object->getAccess(),
            'document' => $this->normalizer->normalize($object->getDocument(), $format, $context),
            'created' => $object->getCreatedDate()->format(ApiSdk::DATE_FORMAT),
        ];

        if ($object->getHighlight()) {
            $data['highlight'] = $object->getHighlight();
        }

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getParents()->notEmpty()) {
            $data['parents'] = $object->getParents()->toArray();
        }

        if ($object->getContent()->notEmpty()) {
            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Annotation;
    }
}
