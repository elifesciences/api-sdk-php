<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class Collection
{
    private $id;
    private $title;
    private $impactStatement;
    private $publishedDate;

    public function __construct(
        $id,
        $title,
        PromiseInterface $subTitle,
        $impactStatement,
        DateTimeImmutable $publishedDate,
        PromiseInterface $banner,
        Image $thumbnail,
        Sequence $subjects
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->impactStatement = $impactStatement;
        $this->publishedDate = $publishedDate;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
        $this->subjects = $subjects;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getSubTitle() : string
    {
        return $this->subTitle->wait();
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
        return $this->publishedDate; 
    }

    public function getBanner() : Image
    {
        return $this->banner->wait();
    }

    public function getThumbnail() : Image
    {
        return $this->thumbnail; 
    }

    public function withSubjects(Sequence $subjects) : Collection
    {
        return new self(
            $this->id,
            $this->title,
            $this->subTitle,
            $this->impactStatement,
            $this->publishedDate,
            $this->banner,
            $this->thumbnail,
            $subjects
        );
    }

    public function getSubjects() : Sequence
    {
        return $this->subjects;
    }
}
