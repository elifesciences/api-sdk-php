<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Annotation;
use eLife\ApiSdk\Model\AnnotationDocument;
use eLife\ApiSdk\Model\Block;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class AnnotationNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    /**
     * @param $data
     * @param $type
     * @param $format
     * @param array $context
     * @return Annotation
     * @throws ExceptionInterface
     */
    public function denormalize($data, $type, $format = null, array $context = []) : Annotation
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
            new ArraySequence($data['ancestors'] ?? []),
            $data['highlight'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['created']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['content']
        );
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
        return Annotation::class === $type;
    }

    /**
     * @param Annotation $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'id' => $data->getId(),
            'access' => $data->getAccess(),
            'document' => $this->normalizer->normalize($data->getDocument(), $format, $context),
            'created' => $data->getCreatedDate()->format(ApiSdk::DATE_FORMAT),
        ];

        if ($data->getHighlight()) {
            $arr['highlight'] = $data->getHighlight();
        }

        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getAncestors()->notEmpty()) {
            $arr['ancestors'] = $data->getAncestors()->toArray();
        }

        if ($data->getContent()->notEmpty()) {
            $arr['content'] = $data->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $arr;
    }

    /**
     * @param $data
     * @param $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Annotation;
    }

    /**
     * @param string|null $format
     * @return true[]
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Annotation::class => true,
        ];
    }
}
