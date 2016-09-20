<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Author;
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
        if (empty($author['onBehalfOf'])) {
            $onBehalfOf = null;
        } else {
            $onBehalfOf = $this->denormalizer->denormalize($author['onBehalfOf'], Place::class, $format,
                $context);
        }

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
            PersonAuthor::class === $type
            ||
            (Author::class === $type && 'person' === $data['type']);
    }

    /**
     * @param PersonAuthor $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PersonAuthor;
    }
}
