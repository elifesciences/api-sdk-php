<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class DataReference implements Reference
{
    private $id;
    private $date;
    private $discriminator;
    private $authors;
    private $authorsEtAl;
    private $compilers;
    private $compilersEtAl;
    private $curators;
    private $curatorsEtAl;
    private $title;
    private $source;
    private $dataId;
    private $assigningAuthority;
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
        array $compilers,
        bool $compilersEtAl,
        array $curators,
        bool $curatorsEtAl,
        string $title,
        string $source,
        string $dataId = null,
        Place $assigningAuthority = null,
        string $doi = null,
        string $uri = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->discriminator = $discriminator;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->compilers = $compilers;
        $this->compilersEtAl = $compilersEtAl;
        $this->curators = $curators;
        $this->curatorsEtAl = $curatorsEtAl;
        $this->title = $title;
        $this->source = $source;
        $this->dataId = $dataId;
        $this->assigningAuthority = $assigningAuthority;
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

    /**
     * @return AuthorEntry[]
     */
    public function getCompilers() : array
    {
        return $this->compilers;
    }

    public function compilersEtAl(): bool
    {
        return $this->compilersEtAl;
    }

    /**
     * @return AuthorEntry[]
     */
    public function getCurators() : array
    {
        return $this->curators;
    }

    public function curatorsEtAl(): bool
    {
        return $this->curatorsEtAl;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string|null
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * @return Place|null
     */
    public function getAssigningAuthority()
    {
        return $this->assigningAuthority;
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
