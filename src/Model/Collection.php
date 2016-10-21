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

    public function __construct($id, $title, $impactStatement, DateTimeImmutable $publishedDate, PromiseInterface $banner, Image $thumbnail, Sequence $subjects = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->publishedDate = $publishedDate;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
        if ($subjects === null) {
            $subjects = new ArraySequence([]);
        }
        $this->subjects = $subjects;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    public function getPublishedDate()
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
