<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class HighlightNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Highlight
    {
        if (!empty($data['image'])) {
            $data['image'] = $this->denormalizer->denormalize($data['image'], Image::class, $format, $context);
        }

        $data['item'] = $this->denormalizer->denormalize($data['item'], Model::class, $format, ['snippet' => true] + $context);

        return new Highlight(
            $data['title'],
            $data['image'] ?? null,
            $data['item'],
            $data['impactStatement'] ?? null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Highlight::class === $type;
    }


    /**
     * @param Highlight $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $arr = [
            'title' => $data->getTitle(),
            'item' => $normalizationHelper->normalizeToSnippet($data->getItem(), ['type' => true] + $context),
            'impactStatement' => $data->getImpactStatement(),
        ];

        if ($data->getThumbnail()) {
            $arr['image'] = $this->normalizer->normalize($data->getThumbnail(), $format, $context);
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Highlight;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Highlight::class => true,
        ];
    }
}
