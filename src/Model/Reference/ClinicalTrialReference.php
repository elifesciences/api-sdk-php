<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Reference;

final class ClinicalTrialReference implements Reference
{
    const AUTHOR_TYPE_AUTHORS = 'authors';
    const AUTHOR_TYPE_COLLABORATORS = 'collaborators';
    const AUTHOR_TYPE_SPONSORS = 'sponsors';

    private $authors;
    private $authorsEtAl;
    private $authorsType;
    private $title;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        array $authors,
        bool $authorsEtAl,
        string $authorsType,
        string $title,
        string $uri
    ) {
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->authorsType = $authorsType;
        $this->title = $title;
        $this->uri = $uri;
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
