<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;

final class ArticlePreprint implements Model, HasPublishedDate
{
    private $description;
    private $uri;
    private $date;

    /**
     * @internal
     */
    public function __construct(string $description, string $uri, DateTimeImmutable $date)
    {
        $this->description = $description;
        $this->uri = $uri;
        $this->date = $date;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getPublishedDate() : DateTimeImmutable
    {
        return $this->date;
    }
}
