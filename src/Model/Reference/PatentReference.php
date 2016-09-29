<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Reference;

final class PatentReference implements Reference
{
    private $inventors;
    private $inventorsEtAl;
    private $assignees;
    private $assigneesEtAl;
    private $title;
    private $patentType;
    private $country;
    private $number;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        array $inventors,
        bool $inventorsEtAl,
        array $assignees,
        bool $assigneesEtAl,
        string $title,
        string $patentType,
        string $country,
        string $number = null,
        string $uri = null
    ) {
        $this->inventors = $inventors;
        $this->inventorsEtAl = $inventorsEtAl;
        $this->assignees = $assignees;
        $this->assigneesEtAl = $assigneesEtAl;
        $this->title = $title;
        $this->patentType = $patentType;
        $this->country = $country;
        $this->number = $number;
        $this->uri = $uri;
    }

    /**
     * @return AuthorEntry[]
     */
    public function getInventors() : array
    {
        return $this->inventors;
    }

    public function inventorsEtAl(): bool
    {
        return $this->inventorsEtAl;
    }

    /**
     * @return AuthorEntry[]
     */
    public function getAssignees() : array
    {
        return $this->assignees;
    }

    public function assigneesEtAl(): bool
    {
        return $this->assigneesEtAl;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPatentType() : string
    {
        return $this->patentType;
    }

    public function getCountry() : string
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }
}
