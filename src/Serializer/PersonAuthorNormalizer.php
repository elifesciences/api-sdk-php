<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PersonAuthorNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : PersonAuthor
    {
        return new PersonAuthor(
            $this->denormalizer->denormalize($data, Person::class, $format, $context),
            $data['deceased'] ?? false,
            array_map(function (array $affiliation) use ($format, $context) {
                return $this->denormalizer->denormalize($affiliation, Place::class, $format, $context);
            }, $data['affiliations'] ?? []),
            $data['competingInterests'] ?? null,
            $data['contribution'] ?? null,
            $data['emailAddresses'] ?? [],
            $data['equalContributionGroups'] ?? [],
            $data['phoneNumbers'] = [],
            array_map(function (array $address) use ($format, $context) {
                return $this->denormalizer->denormalize($address, Address::class, $format, $context);
            }, $data['postalAddresses'] ?? [])
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            PersonAuthor::class === $type
            ||
            (AuthorEntry::class === $type && 'person' === $data['type']);
    }

    /**
     * @param PersonAuthor $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'person',
            'name' => [
                'preferred' => $object->getPreferredName(),
                'index' => $object->getIndexName(),
            ],
        ];

        if ($object->getOrcid()) {
            $data['orcid'] = $object->getOrcid();
        }

        if ($object->isDeceased()) {
            $data['deceased'] = $object->isDeceased();
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
            $data['postalAddresses'] = $object->getPostalAddresses();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PersonAuthor;
    }
}
