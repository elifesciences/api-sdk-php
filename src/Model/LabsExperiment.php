<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class LabsExperiment implements Model, HasBanner, HasContent, HasImpactStatement, HasThumbnail
{
    private $number;
    private $title;
    private $published;
    private $impactStatement;
    private $banner;
    private $thumbnail;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        int $number,
        string $title,
        DateTimeImmutable $published,
        string $impactStatement = null,
        PromiseInterface $banner,
        Image $thumbnail,
        Sequence $content
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->published = $published;
        $this->impactStatement = $impactStatement;
        $this->banner = $banner;
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

    public function getBanner() : Image
    {
        return $this->banner->wait();
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
