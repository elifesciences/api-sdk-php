<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class ThesisReference implements Reference
{
    private $date;
    private $author;
    private $title;
    private $publisher;
    private $doi;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        ReferenceDate $date,
        PersonDetails $author,
        string $title,
        Place $publisher,
        string $doi = null,
        string $uri = null
    ) {
        $this->date = $date;
        $this->author = $author;
        $this->title = $title;
        $this->publisher = $publisher;
        $this->doi = $doi;
        $this->uri = $uri;
    }

    public function getDate() : ReferenceDate
    {
        return $this->date;
    }

    public function getAuthor() : PersonDetails
    {
        return $this->author;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPublisher() : Place
    {
        return $this->publisher;
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
