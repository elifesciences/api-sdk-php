<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class PeriodicalReference implements Reference
{
    private $authors;
    private $authorsEtAl;
    private $articleTitle;
    private $periodical;
    private $volume;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        array $authors,
        bool $authorsEtAl,
        string $articleTitle,
        Place $periodical,
        string $volume = null,
        string $uri = null
    ) {
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->articleTitle = $articleTitle;
        $this->periodical = $periodical;
        $this->volume = $volume;
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

    public function getPeriodical() : Place
    {
        return $this->periodical;
    }

    /**
     * @return string|null
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }
}
