<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reviewer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ReviewerNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Reviewer
    {
        return new Reviewer(
            $this->denormalizer->denormalize($data, PersonDetails::class, $format, $context),
            $data['role'],
            array_map(function (array $affiliation) use ($format, $context) {
                return $this->denormalizer->denormalize($affiliation, Place::class, $format, $context);
            }, $data['affiliations'] ?? [])
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return Reviewer::class === $type;
    }

    /**
     * @param Reviewer $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'name' => [
                'preferred' => $object->getPreferredName(),
                'index' => $object->getIndexName(),
            ],
            'role' => $object->getRole(),
        ];

        if ($object->getOrcid()) {
            $data['orcid'] = $object->getOrcid();
        }

        if ($object->getAffiliations()) {
            $data['affiliations'] = array_map(function (Place $place) use ($format, $context) {
                return $this->normalizer->normalize($place, $format, $context);
            }, $object->getAffiliations());
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Reviewer;
    }
}
