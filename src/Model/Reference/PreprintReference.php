<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Reference;

final class PreprintReference implements Reference
{
    private $id;
    private $date;
    private $authors;
    private $authorsEtAl;
    private $articleTitle;
    private $source;
    private $doi;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        ReferenceDate $date,
        array $authors,
        bool $authorsEtAl,
        string $articleTitle,
        string $source,
        string $doi = null,
        string $uri = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->articleTitle = $articleTitle;
        $this->source = $source;
        $this->doi = $doi;
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

    public function getArticleTitle(): string
    {
        return $this->articleTitle;
    }

    public function getSource() : string
    {
        return $this->source;
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
