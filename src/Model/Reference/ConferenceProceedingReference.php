<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class ConferenceProceedingReference implements Reference
{
    private $authors;
    private $authorsEtAl;
    private $articleTitle;
    private $conference;
    private $doi;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        array $authors,
        bool $authorsEtAl,
        string $articleTitle,
        Place $conference,
        string $doi = null,
        string $uri = null
    ) {
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->articleTitle = $articleTitle;
        $this->conference = $conference;
        $this->doi = $doi;
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

    public function getArticleTitle(): string
    {
        return $this->articleTitle;
    }

    public function getConference() : Place
    {
        return $this->conference;
    }

    /**
     * @return string|null
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }
}
