<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class Person implements Model, HasId, HasIdentifier, HasThumbnail
{
    private $id;
    private $details;
    private $givenNames;
    private $surname;
    private $type;
    private $typeLabel;
    private $image;
    private $affilitations;
    private $research;
    private $profile;
    private $competingInterests;
    private $emailAddresses;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        PersonDetails $details,
        PromiseInterface $givenNames,
        PromiseInterface $surname,
        string $type,
        string $typeLabel,
        Image $image = null,
        Sequence $affiliations,
        PromiseInterface $research,
        Sequence $profile,
        PromiseInterface $competingInterests,
        Sequence $emailAddresses
    ) {
        $this->id = $id;
        $this->details = $details;
        $this->givenNames = $givenNames;
        $this->surname = $surname;
        $this->type = $type;
        $this->typeLabel = $typeLabel;
        $this->image = $image;
        $this->affilitations = $affiliations;
        $this->research = $research;
        $this->profile = $profile;
        $this->competingInterests = $competingInterests;
        $this->emailAddresses = $emailAddresses;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::person($this->id);
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
     * @return string|null
     */
    public function getGivenNames()
    {
        return $this->givenNames->wait();
    }

    /**
     * @return string|null
     */
    public function getSurname()
    {
        return $this->surname->wait();
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getTypeLabel() : string
    {
        return $this->typeLabel;
    }

    /**
     * @return Image|null
     */
    public function getThumbnail()
    {
        return $this->image;
    }

    /**
     * @return Sequence|Place[]
     */
    public function getAffiliations() : Sequence
    {
        return $this->affilitations;
    }

    /**
     * @return PersonResearch|null
     */
    public function getResearch()
    {
        return $this->research->wait();
    }

    /**
     * @return Sequence|Block[]
     */
    public function getProfile() : Sequence
    {
        return $this->profile;
    }

    /**
     * @return string|null
     */
    public function getCompetingInterests()
    {
        return $this->competingInterests->wait();
    }

    /**
     * @return Sequence|AccessControl[]
     */
    public function getEmailAddresses() : Sequence
    {
        return $this->emailAddresses;
    }
}
