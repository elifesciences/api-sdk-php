<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class ThesisReference implements Reference
{
    private $author;
    private $title;
    private $publisher;
    private $doi;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        Person $author,
        string $title,
        Place $publisher,
        string $doi = null,
        string $uri = null
    ) {
        $this->author = $author;
        $this->title = $title;
        $this->publisher = $publisher;
        $this->doi = $doi;
        $this->uri = $uri;
    }

    public function getAuthor() : Person
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
