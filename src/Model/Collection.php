<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class Collection implements Model, HasBanner, HasId, HasImpactStatement, HasSubjects, HasThumbnail
{
    private $id;
    private $title;
    private $subTitle;
    private $impactStatement;
    private $publishedDate;
    private $banner;
    private $thumbnail;
    private $subjects;
    private $selectedCurator;
    private $selectedCuratorEtAl;
    private $curators;
    private $content;
    private $relatedContent;
    private $podcastEpisodes;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        PromiseInterface $subTitle,
        string $impactStatement = null,
        DateTimeImmutable $publishedDate,
        PromiseInterface $banner,
        Image $thumbnail,
        Sequence $subjects,
        Person $selectedCurator,
        bool $selectedCuratorEtAl,
        Sequence $curators,
        Sequence $content,
        Sequence $relatedContent,
        Sequence $podcastEpisodes
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->impactStatement = $impactStatement;
        $this->publishedDate = $publishedDate;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
        $this->subjects = $subjects;
        $this->selectedCurator = $selectedCurator;
        $this->selectedCuratorEtAl = $selectedCuratorEtAl;
        $this->curators = $curators;
        $this->content = $content;
        $this->relatedContent = $relatedContent;
        $this->podcastEpisodes = $podcastEpisodes;
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
    public function getSubTitle()
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

    /**
     * @return Sequence|Subject[]
     */
    public function getSubjects() : Sequence
    {
        return $this->subjects;
    }

    public function getSelectedCurator() : Person
    {
        return $this->selectedCurator;
    }

    public function selectedCuratorEtAl() : bool
    {
        return $this->selectedCuratorEtAl;
    }

    /**
     * @return Sequence|Person[]
     */
    public function getCurators() : Sequence
    {
        return $this->curators;
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
