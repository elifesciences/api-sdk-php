<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\Model\Image;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnualReportNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : AnnualReport
    {
        return new AnnualReport(
            $data['year'],
            $data['uri'],
            $data['title'],
            $data['impactStatement'] ?? null,
            $this->denormalizer->denormalize($data['image'], Image::class, $format, $context)
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return AnnualReport::class === $type;
    }

    /**
     * @param AnnualReport $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'year' => $object->getYear(),
            'uri' => $object->getUri(),
            'title' => $object->getTitle(),
            'image' => $this->normalizer->normalize($object->getImage(), $format, $context),
        ];

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof AnnualReport;
    }
}
