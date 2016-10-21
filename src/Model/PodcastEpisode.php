<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class PodcastEpisode
{
    private $number;
    private $title;
    private $impactStatement;
    private $published;
    private $banner;
    private $thumbnail;
    private $sources;
    private $subjects;
    private $chapters;

    /**
     * @internal
     */
    public function __construct(
        int $number,
        string $title,
        string $impactStatement = null,
        DateTimeImmutable $published,
        PromiseInterface $banner,
        Image $thumbnail,
        array $sources,
        Sequence $subjects,
        Sequence $chapters
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->published = $published;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
        $this->sources = $sources;
        $this->subjects = $subjects;
        $this->chapters = $chapters;
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
     * @return PodcastEpisodeSource[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @return Sequence|Subject[]
     */
    public function getSubjects() : Sequence
    {
        return $this->subjects;
    }

    /**
     * @return Sequence|PodcastEpisodeChapter[]
     */
    public function getChapters() : Sequence
    {
        return $this->chapters;
    }
}
