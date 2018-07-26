<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\Model\Image;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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
            $data['pdf'] ?? null,
            $data['title'],
            $data['impactStatement'] ?? null
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
        ];

        if ($object->getPdf()) {
            $data['pdf'] = $object->getPdf();
        }

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
