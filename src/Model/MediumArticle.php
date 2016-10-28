<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;

final class MediumArticle implements Model
{
    private $uri;
    private $title;
    private $impactStatement;
    private $published;
    private $image;

    /**
     * @internal
     */
    public function __construct(
        string $uri,
        string $title,
        string $impactStatement = null,
        DateTimeImmutable $published,
        Image $image = null
    ) {
        $this->uri = $uri;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->published = $published->setTimezone(new DateTimeZone('UTC'));
        $this->image = $image;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    public function getPublishedDate(): DateTimeImmutable
    {
        return $this->published;
    }

    /**
     * @return Image|null
     */
    public function getImage()
    {
        return $this->image;
    }
}
