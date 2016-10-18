<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;

final class LabsExperiment
{
    private $number;
    private $title;
    private $published;
    private $impactStatement;
    private $image;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        int $number,
        string $title,
        DateTimeImmutable $published,
        string $impactStatement = null,
        Image $image,
        Sequence $content
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->published = $published;
        $this->impactStatement = $impactStatement;
        $this->image = $image;
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

    public function getImage() : Image
    {
        return $this->image;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }
}
