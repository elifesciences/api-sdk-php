<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Reference;

final class WebReference implements Reference
{
    private $authors;
    private $authorsEtAl;
    private $title;
    private $uri;
    private $website;

    /**
     * @internal
     */
    public function __construct(
        array $authors,
        bool $authorsEtAl,
        string $title,
        string $uri,
        string $website = null
    ) {
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->title = $title;
        $this->uri = $uri;
        $this->website = $website;
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

    public function getUri() : string
    {
        return $this->uri;
    }

    /**
     * @return string|null
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
