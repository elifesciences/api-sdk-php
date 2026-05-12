<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\MediaContact;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class MediaContactNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : MediaContact
    {
        $data['affiliations'] = array_map(function (array $affiliation) use ($format, $context) {
            return $this->denormalizer->denormalize($affiliation, Place::class, $format, $context);
        }, $data['affiliations'] ?? []);

        return new MediaContact(
            $this->denormalizer->denormalize($data, PersonDetails::class, $format, $context),
            $data['affiliations'],
            $data['emailAddresses'] ?? [],
            $data['phoneNumbers'] ?? []
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return MediaContact::class === $type;
    }

    /**
     * @param MediaContact $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = $this->normalizer->normalize($data->getDetails());

        if (count($data->getAffiliations())) {
            $arr['affiliations'] = array_map(function (Place $place) use ($format, $context) {
                return $this->normalizer->normalize($place, $format, $context);
            }, $data->getAffiliations());
        }

        if (count($data->getEmailAddresses())) {
            $arr['emailAddresses'] = $data->getEmailAddresses();
        }

        if (count($data->getPhoneNumbers())) {
            $arr['phoneNumbers'] = $data->getPhoneNumbers();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof MediaContact;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MediaContact::class => true,
        ];
    }
}
