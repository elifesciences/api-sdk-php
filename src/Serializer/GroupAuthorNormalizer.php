<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\PersonAuthor;

final class GroupAuthorNormalizer extends AuthorNormalizer
{
    public function denormalizeAuthor(
        array $data,
        string $class,
        string $format = null,
        array $context = []
    ) : Author {
        return new GroupAuthor(
            $data['name'],
            new ArrayCollection(array_map(function (array $person) use ($format, $context) {
                return $this->denormalizer->denormalize($person, PersonAuthor::class, $format, $context);
            }, $data['people'] ?? [])),
            new ArrayCollection(array_map(function (array $group) use ($format, $context) {
                return $this->denormalizer->denormalize($group, GroupAuthor::class, $format, $context);
            }, $data['groups'] ?? [])),
            $data['affiliations'],
            $data['competingInterests'] ?? null,
            $data['contribution'] ?? null,
            $data['emailAddresses'] ?? [],
            $data['equalContributionGroups'] ?? [],
            $data['phoneNumbers'] = [],
            $data['postalAddresses']
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
    protected function normalizeAuthor(Author $object, array $data, $format = null, array $context = []) : array
    {
        $data['type'] = 'group';
        $data['name'] = $object->getName();

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

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof GroupAuthor;
    }
}
