<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;

final class PodcastEpisode
{
    private $number;
    private $title;
    private $impactStatement;
    private $published;
    private $image;
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
        Image $image,
        array $sources,
        Sequence $subjects,
        Sequence $chapters
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->published = $published;
        $this->image = $image;
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

    public function getImage() : Image
    {
        return $this->image;
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
