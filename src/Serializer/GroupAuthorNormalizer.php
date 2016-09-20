<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Author;
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
        if (empty($data['onBehalfOf'])) {
            $onBehalfOf = null;
        } else {
            $onBehalfOf = $this->denormalizer->denormalize($data['onBehalfOf'], Place::class, $format,
                $context);
        }

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
            $onBehalfOf,
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
            (Author::class === $type && 'group' === $data['type']);
    }

    /**
     * @param GroupAuthor $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof GroupAuthor;
    }
}
