<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class PersonResearch
{
    private $expertises;
    private $focuses;
    private $organisms;

    /**
     * @internal
     */
    public function __construct(Sequence $expertises, array $focuses, array $organisms)
    {
        $this->expertises = $expertises;
        $this->focuses = $focuses;
        $this->organisms = $organisms;
    }

    /**
     * @return Sequence|Subject[]
     */
    public function getExpertises() : Sequence
    {
        return $this->expertises;
    }

    public function getFocuses() : array
    {
        return $this->focuses;
    }

    public function getOrganisms() : array
    {
        return $this->organisms;
    }
}
