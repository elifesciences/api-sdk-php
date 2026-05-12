<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AnnualReport;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class AnnualReportNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    /**
     * @param $data
     * @param $type
     * @param $format
     * @param array $context
     * @return AnnualReport
     */
    public function denormalize($data, $type, $format = null, array $context = []) : AnnualReport
    {
        return new AnnualReport(
            $data['year'],
            $data['uri'],
            $data['pdf'] ?? null,
            $data['title'],
            $data['impactStatement'] ?? null
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
        return AnnualReport::class === $type;
    }

    /**
     * @param AnnualReport $data
     * @param $format
     * @param array $context
     * @return array
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'year' => $data->getYear(),
            'uri' => $data->getUri(),
            'title' => $data->getTitle(),
        ];

        if ($data->getPdf()) {
            $arr['pdf'] = $data->getPdf();
        }

        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
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
        return $data instanceof AnnualReport;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AnnualReport::class => true,
        ];
    }
}
