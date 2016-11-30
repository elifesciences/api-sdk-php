<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class Person implements Model, HasId, HasThumbnail
{
    private $id;
    private $details;
    private $type;
    private $image;
    private $research;
    private $profile;
    private $competingInterests;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        PersonDetails $details,
        string $type,
        Image $image = null,
        PromiseInterface $research,
        Sequence $profile,
        PromiseInterface $competingInterests
    ) {
        $this->id = $id;
        $this->details = $details;
        $this->type = $type;
        $this->image = $image;
        $this->research = $research;
        $this->profile = $profile;
        $this->competingInterests = $competingInterests;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getDetails() : PersonDetails
    {
        return $this->details;
    }

    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return Image|null
     */
    public function getThumbnail()
    {
        return $this->image;
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
}
