<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reviewer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ReviewerNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Reviewer
    {
        return new Reviewer(
            $this->denormalizer->denormalize($data, PersonDetails::class, $format, $context),
            $data['role'],
            array_map(function (array $affiliation) use ($format, $context) {
                return $this->denormalizer->denormalize($affiliation, Place::class, $format, $context);
            }, $data['affiliations'] ?? [])
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Reviewer::class === $type;
    }

    /**
     * @param Reviewer $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'name' => [
                'preferred' => $data->getPreferredName(),
                'index' => $data->getIndexName(),
            ],
            'role' => $data->getRole(),
        ];

        if ($data->getOrcid()) {
            $arr['orcid'] = $data->getOrcid();
        }

        if ($data->getAffiliations()) {
            $arr['affiliations'] = array_map(function (Place $place) use ($format, $context) {
                return $this->normalizer->normalize($place, $format, $context);
            }, $data->getAffiliations());
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Reviewer;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Reviewer::class => false,
        ];
    }
}
