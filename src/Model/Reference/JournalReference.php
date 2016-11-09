<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class JournalReference implements Reference
{
    private $id;
    private $date;
    private $authors;
    private $authorsEtAl;
    private $articleTitle;
    private $journal;
    private $pages;
    private $volume;
    private $doi;
    private $pmid;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        ReferenceDate $date,
        array $authors,
        bool $authorsEtAl,
        string $articleTitle,
        Place $journal,
        ReferencePages $pages,
        string $volume = null,
        string $doi = null,
        int $pmid = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->articleTitle = $articleTitle;
        $this->journal = $journal;
        $this->pages = $pages;
        $this->volume = $volume;
        $this->doi = $doi;
        $this->pmid = $pmid;
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

    public function getJournal() : Place
    {
        return $this->journal;
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
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * @return int|null
     */
    public function getPmid()
    {
        return $this->pmid;
    }
}
