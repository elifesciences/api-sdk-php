<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class SoftwareReference implements Reference
{
    private $authors;
    private $authorsEtAl;
    private $title;
    private $publisher;
    private $version;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        array $authors,
        bool $authorsEtAl,
        string $title,
        Place $publisher,
        string $version = null,
        string $uri = null
    ) {
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->title = $title;
        $this->publisher = $publisher;
        $this->version = $version;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPublisher() : Place
    {
        return $this->publisher;
    }

    /**
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }
}
