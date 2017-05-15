<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\Sequence;

final class PersonAuthor extends Author
{
    private $person;
    private $biography;
    private $deceased;
    private $role;

    /**
     * @internal
     */
    public function __construct(
        PersonDetails $person,
        Sequence $biography = null,
        bool $deceased = false,
        string $role = null,
        array $additionalInformation = [],
        array $affiliations = [],
        string $competingInterests = null,
        string $contribution = null,
        array $emailAddresses = [],
        array $equalContributionGroups = [],
        array $phoneNumbers = [],
        array $postalAddresses = []
    ) {
        parent::__construct($additionalInformation, $affiliations, $competingInterests, $contribution, $emailAddresses,
            $equalContributionGroups, $phoneNumbers, $postalAddresses);

        $this->person = $person;
        $this->biography = $biography ?? new EmptySequence();
        $this->deceased = $deceased;
        $this->role = $role;
    }

    public function toString() : string
    {
        return $this->getPreferredName();
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

    /**
     * @return Sequence|Block[]
     */
    public function getBiography() : Sequence
    {
        return $this->biography;
    }

    public function isDeceased() : bool
    {
        return $this->deceased;
    }

    /**
     * @return string|null
     */
    public function getRole()
    {
        return $this->role;
    }
}
