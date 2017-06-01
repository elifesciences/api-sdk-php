<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;

final class PersonAuthorNormalizer extends AuthorNormalizer
{
    protected function denormalizeAuthor(
        array $data,
        string $class,
        string $format = null,
        array $context = []
    ) : Author {
        return new PersonAuthor(
            $this->denormalizer->denormalize($data, PersonDetails::class, $format, $context),
            new ArraySequence(array_map(function (array $block) use ($format, $context) {
                return $this->denormalizer->denormalize($block, Block::class, $format, $context);
            }, $data['biography'] ?? [])),
            $data['deceased'] ?? false,
            $data['role'] ?? null,
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
            PersonAuthor::class === $type
            ||
            (in_array($type, [AuthorEntry::class, Author::class]) && 'person' === $data['type']);
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

        if ($object->getBiography()->notEmpty()) {
            $data['biography'] = $object->getBiography()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        if ($object->isDeceased()) {
            $data['deceased'] = $object->isDeceased();
        }

        if ($object->getRole()) {
            $data['role'] = $object->getRole();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PersonAuthor;
    }
}
