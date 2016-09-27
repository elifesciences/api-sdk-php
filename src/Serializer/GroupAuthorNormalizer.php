<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GroupAuthorNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) :GroupAuthor
    {
        return new GroupAuthor(
            $data['name'],
            new ArrayCollection(array_map(function (array $person) use ($format, $context) {
                return $this->denormalizer->denormalize($person, PersonAuthor::class, $format, $context);
            }, $data['people'] ?? [])),
            new ArrayCollection(array_map(function (array $group) use ($format, $context) {
                return $this->denormalizer->denormalize($group, GroupAuthor::class, $format, $context);
            }, $data['groups'] ?? [])),
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
            GroupAuthor::class === $type
            ||
            (AuthorEntry::class === $type && 'group' === $data['type']);
    }

    /**
     * @param GroupAuthor $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'name' => $object->getName(),
        ];

        if (count($object->getPeople())) {
            $data['people'] = $object->getPeople()->map(function (PersonAuthor $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            });
        }

        if (count($object->getGroups())) {
            $data['groups'] = $object->getGroups()->map(function (GroupAuthor $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            });
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
        return $data instanceof GroupAuthor;
    }
}
