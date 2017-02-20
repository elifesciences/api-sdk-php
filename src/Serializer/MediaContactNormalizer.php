<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\MediaContact;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MediaContactNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : MediaContact
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

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return MediaContact::class === $type;
    }

    /**
     * @param MediaContact $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = $this->normalizer->normalize($object->getDetails());

        if (count($object->getAffiliations())) {
            $data['affiliations'] = array_map(function (Place $place) use ($format, $context) {
                return $this->normalizer->normalize($place, $format, $context);
            }, $object->getAffiliations());
        }

        if (count($object->getEmailAddresses())) {
            $data['emailAddresses'] = $object->getEmailAddresses();
        }

        if (count($object->getPhoneNumbers())) {
            $data['phoneNumbers'] = $object->getPhoneNumbers();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof MediaContact;
    }
}
