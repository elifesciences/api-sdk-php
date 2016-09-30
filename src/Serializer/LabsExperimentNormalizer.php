<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsExperiment;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class LabsExperimentNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : LabsExperiment
    {
        $data['content'] = new PromiseCollection(promise_for($data['content'])
            ->then(function (array $blocks) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $blocks);
            }));

        return new LabsExperiment(
            $data['number'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            $data['impactStatement'] ?? null,
            $this->denormalizer->denormalize($data['image'], Image::class, $format, $context),
            $data['content']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return LabsExperiment::class === $type;
    }

    /**
     * @param LabsExperiment $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'number' => $object->getNumber(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(DATE_ATOM),
            'image' => $this->normalizer->normalize($object->getImage(), $format, $context),
        ];

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (empty($context['snippet'])) {
            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof LabsExperiment;
    }
}
