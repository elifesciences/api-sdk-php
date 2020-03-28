<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class RegionalCollection implements Model, HasBanner, HasId, HasIdentifier, HasImpactStatement, HasSubjects, HasPublishedDate, HasThumbnail, HasUpdatedDate
{
    private $id;
    private $title;
    private $impactStatement;
    private $publishedDate;
    private $updatedDate;
    private $banner;
    private $thumbnail;
    private $subjects;
    private $editors;
    private $summary;
    private $content;
    private $relatedContent;
    private $podcastEpisodes;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        string $impactStatement = null,
        DateTimeImmutable $publishedDate,
        DateTimeImmutable $updatedDate = null,
        PromiseInterface $banner,
        Image $thumbnail,
        Sequence $subjects,
        Sequence $editors,
        Sequence $summary,
        Sequence $content,
        Sequence $relatedContent,
        Sequence $podcastEpisodes
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->publishedDate = $publishedDate;
        $this->updatedDate = $updatedDate;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
        $this->subjects = $subjects;
        $this->editors = $editors;
        $this->summary = $summary;
        $this->content = $content;
        $this->relatedContent = $relatedContent;
        $this->podcastEpisodes = $podcastEpisodes;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::regionalCollection($this->id);
    }

    public function getId() : string
    {
        return $this->id;
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
        return $this->publishedDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
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
     * @return Sequence|Subject[]
     */
    public function getSubjects() : Sequence
    {
        return $this->subjects;
    }

    /**
     * @return Sequence|Person[]
     */
    public function getEditors() : Sequence
    {
        return $this->editors;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getSummary() : Sequence
    {
        return $this->summary;
    }

    /**
     * @return Sequence|Model[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }

    /**
     * @return Sequence|Model[]
     */
    public function getRelatedContent() : Sequence
    {
        return $this->relatedContent;
    }

    /**
     * @return Sequence|PodcastEpisode[]
     */
    public function getPodcastEpisodes() : Sequence
    {
        return $this->podcastEpisodes;
    }
}
