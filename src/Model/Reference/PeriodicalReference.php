<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class PeriodicalReference implements Reference
{
    private $date;
    private $authors;
    private $authorsEtAl;
    private $articleTitle;
    private $periodical;
    private $pages;
    private $volume;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        ReferenceDate $date,
        array $authors,
        bool $authorsEtAl,
        string $articleTitle,
        Place $periodical,
        ReferencePages $pages,
        string $volume = null,
        string $uri = null
    ) {
        $this->date = $date;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->articleTitle = $articleTitle;
        $this->periodical = $periodical;
        $this->pages = $pages;
        $this->volume = $volume;
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

    public function getArticleTitle(): string
    {
        return $this->articleTitle;
    }

    public function getPeriodical() : Place
    {
        return $this->periodical;
    }

    public function getPages() : ReferencePages
    {
        return $this->pages;
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
