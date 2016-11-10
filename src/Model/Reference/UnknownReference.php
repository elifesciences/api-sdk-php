<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Reference;

final class UnknownReference implements Reference
{
    private $id;
    private $date;
    private $authors;
    private $authorsEtAl;
    private $title;
    private $details;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        ReferenceDate $date,
        array $authors,
        bool $authorsEtAl,
        string $title,
        string $details = null,
        string $uri = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->title = $title;
        $this->details = $details;
        $this->uri = $uri;
    }

    public function getId() : string
    {
        return $this->id;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }
}
