<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class Profile implements Model, HasId, HasIdentifier
{
    private $id;
    private $details;
    private $affiliations;
    private $emailAddresses;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        PersonDetails $details,
        Sequence $affiliations,
        Sequence $emailAddresses
    ) {
        $this->id = $id;
        $this->details = $details;
        $this->affiliations = $affiliations;
        $this->emailAddresses = $emailAddresses;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::profile($this->id);
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getDetails() : PersonDetails
    {
        return $this->details;
    }

    /**
     * @return Sequence|AccessControl[]
     */
    public function getAffiliations() : Sequence
    {
        return $this->affiliations;
    }

    /**
     * @return Sequence|AccessControl[]
     */
    public function getEmailAddresses() : Sequence
    {
        return $this->emailAddresses;
    }
}
