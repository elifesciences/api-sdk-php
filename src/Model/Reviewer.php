<?php

namespace eLife\ApiSdk\Model;

final class Reviewer
{
    private $person;
    private $role;
    private $affiliations;

    /**
     * @internal
     */
    public function __construct(PersonDetails $person, string $role, array $affiliations = [])
    {
        $this->person = $person;
        $this->role = $role;
        $this->affiliations = $affiliations;
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

    public function getRole() : string
    {
        return $this->role;
    }

    /**
     * @return Place[]
     */
    public function getAffiliations() : array
    {
        return $this->affiliations;
    }
}
