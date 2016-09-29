<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;

final class PersonAuthorNormalizer extends AuthorNormalizer
{
    protected function denormalizeAuthor(
        array $data,
        string $class,
        string $format = null,
        array $context = []
    ) : Author {
        return new PersonAuthor(
            $this->denormalizer->denormalize($data, Person::class, $format, $context),
            $data['deceased'] ?? false,
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
            PersonAuthor::class === $type
            ||
            (AuthorEntry::class === $type && 'person' === $data['type']);
    }

    /**
     * @param PersonAuthor $object
     */
    protected function normalizeAuthor(Author $object, array $data, $format = null, array $context = []) : array
    {
        $data['type'] = 'person';
        $data['name'] = [
            'preferred' => $object->getPreferredName(),
            'index' => $object->getIndexName(),
        ];

        if ($object->getOrcid()) {
            $data['orcid'] = $object->getOrcid();
        }

        if ($object->isDeceased()) {
            $data['deceased'] = $object->isDeceased();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PersonAuthor;
    }
}
