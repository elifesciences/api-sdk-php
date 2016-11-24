<?php

namespace eLife\ApiSdk\Model;

final class DataSet
{
    private $id;
    private $date;
    private $authors;
    private $authorsEtAl;
    private $title;
    private $dataId;
    private $details;
    private $doi;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        Date $date,
        array $authors,
        bool $authorsEtAl,
        string $title,
        string $dataId = null,
        string $details = null,
        string $doi = null,
        string $uri = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->title = $title;
        $this->dataId = $dataId;
        $this->details = $details;
        $this->doi = $doi;
        $this->uri = $uri;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): Date
    {
        return $this->date;
    }

    /**
     * @return AuthorEntry[]
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function authorsEtAl(): bool
    {
        return $this->authorsEtAl;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * @return string|null
     */
    public function getDetails()
    {
        return $this->details;
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
