<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\MediumArticle;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MediumArticleNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : MediumArticle
    {
        return new MediumArticle(
            $data['uri'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $data['published']),
            !empty($data['image']) ? $this->denormalizer->denormalize($data['image'], Image::class, $format,
                $context) : null
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return MediumArticle::class === $type;
    }

    /**
     * @param MediumArticle $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'uri' => $object->getUri(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format('Y-m-d\TH:i:s\Z'),
        ];

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if ($object->getImage()) {
            $data['image'] = $this->normalizer->normalize($object->getImage(), $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof MediumArticle;
    }
}
