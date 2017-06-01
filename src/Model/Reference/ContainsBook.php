<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;

/**
 * @internal
 */
trait ContainsBook
{
    private $authors;
    private $authorsEtAl;
    private $editors;
    private $editorsEtAl;
    private $bookTitle;
    private $publisher;
    private $volume;
    private $edition;
    private $doi;
    private $pmid;
    private $isbn;

    /**
     * @return AuthorEntry[]
     */
    public function getAuthors() : array
    {
        return $this->authors;
    }

    public function authorsEtAl() : bool
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

    public function editorsEtAl() : bool
    {
        return $this->editorsEtAl;
    }

    public function getBookTitle() : string
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
