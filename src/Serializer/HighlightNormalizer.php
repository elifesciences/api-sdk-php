<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class HighlightNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Highlight
    {
        if (!empty($data['image'])) {
            $data['image'] = $this->denormalizer->denormalize($data['image'], Image::class, $format, $context);
        }

        $data['item'] = $this->denormalizer->denormalize($data['item'], Model::class, $format, ['snippet' => true] + $context);

        return new Highlight(
            $data['title'],
            $data['authorLine'] ?? null,
            $data['image'] ?? null,
            $data['item']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Highlight::class === $type;
    }

    /**
     * @param Highlight $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [
            'title' => $object->getTitle(),
            'item' => $normalizationHelper->normalizeToSnippet($object->getItem(), ['type' => true] + $context),
        ];

        if ($object->getAuthorLine()) {
            $data['authorLine'] = $object->getAuthorLine();
        }

        if ($object->getThumbnail()) {
            $data['image'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Highlight;
    }
}
