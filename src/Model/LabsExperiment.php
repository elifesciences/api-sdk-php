<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;

final class LabsExperiment implements Model, HasContent, HasImpactStatement, HasPublishedDate, HasThumbnail, HasUpdatedDate
{
    private $number;
    private $title;
    private $published;
    private $updated;
    private $impactStatement;
    private $thumbnail;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        int $number,
        string $title,
        DateTimeImmutable $published,
        DateTimeImmutable $updated = null,
        string $impactStatement = null,
        Image $thumbnail,
        Sequence $content
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->published = $published;
        $this->updated = $updated;
        $this->impactStatement = $impactStatement;
        $this->thumbnail = $thumbnail;
        $this->content = $content;
    }

    public function getNumber() : int
    {
        return $this->number;
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

    public function getPublishedDate() : DateTimeImmutable
    {
        return $this->published;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate()
    {
        return $this->updated;
    }

    public function getThumbnail() : Image
    {
        return $this->thumbnail;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }
}
