<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class BookChapterReference implements Reference
{
    private $date;
    private $authors;
    private $authorsEtAl;
    private $editors;
    private $editorsEtAl;
    private $chapterTitle;
    private $bookTitle;
    private $publisher;
    private $pages;
    private $volume;
    private $edition;
    private $doi;
    private $pmid;
    private $isbn;

    /**
     * @internal
     */
    public function __construct(
        ReferenceDate $date,
        array $authors,
        bool $authorsEtAl,
        array $editors,
        bool $editorsEtAl,
        string $chapterTitle,
        string $bookTitle,
        Place $publisher,
        ReferencePages $pages,
        string $volume = null,
        string $edition = null,
        string $doi = null,
        int $pmid = null,
        string $isbn = null
    ) {
        $this->date = $date;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->editors = $editors;
        $this->editorsEtAl = $editorsEtAl;
        $this->chapterTitle = $chapterTitle;
        $this->bookTitle = $bookTitle;
        $this->publisher = $publisher;
        $this->pages = $pages;
        $this->volume = $volume;
        $this->edition = $edition;
        $this->doi = $doi;
        $this->pmid = $pmid;
        $this->isbn = $isbn;
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

    /**
     * @return AuthorEntry[]
     */
    public function getEditors() : array
    {
        return $this->editors;
    }

    public function editorsEtAl(): bool
    {
        return $this->editorsEtAl;
    }

    public function getChapterTitle() : string
    {
        return $this->chapterTitle;
    }

    public function getBookTitle(): string
    {
        return $this->bookTitle;
    }

    public function getPublisher() : Place
    {
        return $this->publisher;
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
    public function getEdition()
    {
        return $this->edition;
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

    /**
     * @return string|null
     */
    public function getIsbn()
    {
        return $this->isbn;
    }
}
