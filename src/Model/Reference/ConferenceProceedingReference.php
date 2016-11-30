<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class ConferenceProceedingReference implements Reference, HasDoi
{
    private $id;
    private $date;
    private $discriminator;
    private $authors;
    private $authorsEtAl;
    private $articleTitle;
    private $conference;
    private $pages;
    private $doi;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        Date $date,
        string $discriminator = null,
        array $authors,
        bool $authorsEtAl,
        string $articleTitle,
        Place $conference,
        ReferencePages $pages = null,
        string $doi = null,
        string $uri = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->discriminator = $discriminator;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->articleTitle = $articleTitle;
        $this->conference = $conference;
        $this->pages = $pages;
        $this->doi = $doi;
        $this->uri = $uri;
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
     * @return ReferencePages|null
     */
    public function getPages()
    {
        return $this->pages;
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
