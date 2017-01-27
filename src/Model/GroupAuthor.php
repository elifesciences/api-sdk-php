<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class GroupAuthor extends Author
{
    private $name;
    private $people;
    private $groups;

    /**
     * @internal
     */
    public function __construct(
        string $name,
        Sequence $people,
        array $groups = [],
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

        $this->name = $name;
        $this->people = $people;
        $this->groups = $groups;
    }

    public function toString() : string
    {
        return $this->getName();
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPeople() : Sequence
    {
        return $this->people;
    }

    public function getGroups() : array
    {
        return $this->groups;
    }
}
