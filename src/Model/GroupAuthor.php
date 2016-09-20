<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection;

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
        Collection $people,
        Collection $groups,
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

        $this->name = $name;
        $this->people = $people;
        $this->groups = $groups;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPeople() : Collection
    {
        return $this->people;
    }

    public function getGroups() : Collection
    {
        return $this->groups;
    }
}
