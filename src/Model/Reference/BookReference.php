<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class BookReference implements Reference
{
    private $id;
    private $date;
    private $authors;
    private $authorsEtAl;
    private $bookTitle;
    private $publisher;
    private $volume;
    private $edition;
    private $doi;
    private $pmid;
    private $isbn;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        ReferenceDate $date,
        array $authors,
        bool $authorsEtAl,
        string $bookTitle,
        Place $publisher,
        string $volume = null,
        string $edition = null,
        string $doi = null,
        int $pmid = null,
        string $isbn = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->bookTitle = $bookTitle;
        $this->publisher = $publisher;
        $this->volume = $volume;
        $this->edition = $edition;
        $this->doi = $doi;
        $this->pmid = $pmid;
        $this->isbn = $isbn;
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

    public function getBookTitle(): string
    {
        return $this->bookTitle;
    }

    public function getPublisher() : Place
    {
        return $this->publisher;
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
