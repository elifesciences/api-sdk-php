<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Reference;

final class ClinicalTrialReference implements Reference
{
    const AUTHOR_TYPE_AUTHORS = 'authors';
    const AUTHOR_TYPE_COLLABORATORS = 'collaborators';
    const AUTHOR_TYPE_SPONSORS = 'sponsors';

    private $date;
    private $authors;
    private $authorsEtAl;
    private $authorsType;
    private $title;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        ReferenceDate $date,
        array $authors,
        bool $authorsEtAl,
        string $authorsType,
        string $title,
        string $uri
    ) {
        $this->date = $date;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->authorsType = $authorsType;
        $this->title = $title;
        $this->uri = $uri;
    }

    public function getDate() : ReferenceDate
    {
        return $this->date;
    }

    /**
     * @return AuthorEntry[]
     */
    public function getAuthors() : array
    {
        return $this->authors;
    }

    public function authorsEtAl(): bool
    {
        return $this->authorsEtAl;
    }

    public function getAuthorsType(): string
    {
        return $this->authorsType;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
}
