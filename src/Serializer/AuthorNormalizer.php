<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AuthorNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    final public function denormalize($data, $class, $format = null, array $context = []) : Author
    {
        $data['affiliations'] = array_map(function (array $affiliation) use ($format, $context) {
            return $this->denormalizer->denormalize($affiliation, Place::class, $format, $context);
        }, $data['affiliations'] ?? []);

        $data['postalAddresses'] = array_map(function (array $address) use ($format, $context) {
            return $this->denormalizer->denormalize($address, Address::class, $format, $context);
        }, $data['postalAddresses'] ?? []);

        return $this->denormalizeAuthor($data, $class, $format, $context);
    }

    /**
     * @param Author $object
     */
    final public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [];

        if (count($object->getAdditionalInformation())) {
            $data['additionalInformation'] = $object->getAdditionalInformation();
        }

        if (count($object->getAffiliations())) {
            $data['affiliations'] = array_map(function (Place $place) use ($format, $context) {
                return $this->normalizer->normalize($place, $format, $context);
            }, $object->getAffiliations());
        }

        if ($object->getCompetingInterests()) {
            $data['competingInterests'] = $object->getCompetingInterests();
        }
        if ($object->getContribution()) {
            $data['contribution'] = $object->getContribution();
        }

        if (count($object->getEmailAddresses())) {
            $data['emailAddresses'] = $object->getEmailAddresses();
        }

        if (count($object->getEqualContributionGroups())) {
            $data['equalContributionGroups'] = $object->getEqualContributionGroups();
        }

        if (count($object->getPhoneNumbers())) {
            $data['phoneNumbers'] = $object->getPhoneNumbers();
        }

        if (count($object->getPostalAddresses())) {
            $data['postalAddresses'] = array_map(function (Address $address) use ($format, $context) {
                return $this->normalizer->normalize($address, $format, $context);
            }, $object->getPostalAddresses());
        }

        return $this->normalizeAuthor($object, $data, $format, $context);
    }

    abstract protected function denormalizeAuthor(
        array $data,
        string $class,
        string $format = null,
        array $context = []
    ) : Author;

    abstract protected function normalizeAuthor(
        Author $object,
        array $data,
        $format = null,
        array $context = []
    ) : array;
}
