<?php

namespace eLife\ApiSdk\Model;

final class PersonAuthor extends Author
{
    private $person;
    private $deceased;

    /**
     * @internal
     */
    public function __construct(
        Person $person,
        bool $deceased = false,
        array $affiliations = [],
        string $competingInterests = null,
        string $contribution = null,
        array $emailAddresses = [],
        array $equalContributionGroups = [],
        Place $onBehalfOf = null,
        array $phoneNumbers = [],
        array $postalAddresses = []
    ) {
        parent::__construct($affiliations, $competingInterests, $contribution, $emailAddresses,
            $equalContributionGroups, $onBehalfOf, $phoneNumbers, $postalAddresses);

        $this->person = $person;
        $this->deceased = $deceased;
    }

    public function getPreferredName() : string
    {
        return $this->person->getPreferredName();
    }

    public function getIndexName() : string
    {
        return $this->person->getIndexName();
    }

    /**
     * @return string|null
     */
    public function getOrcid()
    {
        return $this->person->getOrcid();
    }

    public function isDeceased() : bool
    {
        return $this->deceased;
    }
}
