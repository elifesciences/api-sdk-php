<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;

final class GroupAuthorNormalizer extends AuthorNormalizer
{
    public function denormalizeAuthor(
        array $data,
        string $class,
        string $format = null,
        array $context = []
    ) : Author {
        foreach ($data['groups'] ?? [] as $key => $group) {
            foreach ($group as $i => $member) {
                $data['groups'][$key][$i] = $this->denormalizer->denormalize($member, PersonDetails::class, $format,
                    $context);
            }
        }

        return new GroupAuthor(
            $data['name'],
            new ArraySequence(array_map(function (array $person) use ($format, $context) {
                return $this->denormalizer->denormalize($person, PersonAuthor::class, $format, $context);
            }, $data['people'] ?? [])),
            $data['groups'] ?? [],
            $data['additionalInformation'] ?? [],
            $data['affiliations'],
            $data['competingInterests'] ?? null,
            $data['contribution'] ?? null,
            $data['emailAddresses'] ?? [],
            $data['equalContributionGroups'] ?? [],
            $data['phoneNumbers'] ?? [],
            $data['postalAddresses']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            GroupAuthor::class === $type
            ||
            (in_array($type, [AuthorEntry::class, Author::class]) && 'group' === $data['type']);
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
            })->toArray();
        }

        if (count($object->getGroups())) {
            $data['groups'] = [];

            foreach ($object->getGroups() as $key => $group) {
                foreach ($group as $i => $member) {
                    $data['groups'][$key][$i] = $this->normalizer->normalize($member, $format, $context);
                }
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof GroupAuthor;
    }
}
