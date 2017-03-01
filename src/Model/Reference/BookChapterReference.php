<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class BookChapterReference implements Reference, HasDoi
{
    use ContainsBook;

    private $id;
    private $date;
    private $discriminator;
    private $chapterTitle;
    private $pages;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        Date $date,
        string $discriminator = null,
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
        $this->id = $id;
        $this->date = $date;
        $this->discriminator = $discriminator;
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

    public function getId() : string
    {
        return $this->id;
    }

    public function getDate() : Date
    {
        return $this->date;
    }

    public function getDiscriminator()
    {
        return $this->discriminator;
    }

    public function getChapterTitle() : string
    {
        return $this->chapterTitle;
    }

    public function getPages() : ReferencePages
    {
        return $this->pages;
    }
}
