<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

class ReviewedPreprint implements HasDoi, HasIdentifier, HasPublishedDate, HasThumbnail
{
    const STAGE_PREVIEW = 'preview';
    const STAGE_PUBLISHED = 'published';

    private $authorLine;
    private $curationLabels;
    private $doi;
    private $elocationId;
    private $id;
    private $indexContent;
    private $published;
    private $pdf;
    private $reviewedDate;
    private $stage;
    private $status;
    private $subjects;
    private $statusDate;
    private $titlePrefix;
    private $title;
    private $image;
    private $volume;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        string $status,
        string $stage,
        string $indexContent = null,
        string $doi = null,
        string $authorLine = null,
        string $titlePrefix = null,
        DateTimeImmutable $published = null,
        DateTimeImmutable $reviewedDate = null,
        DateTimeImmutable $statusDate = null,
        int $volume = null,
        string $elocationId = null,
        string $pdf = null,
        Sequence $subjects = null,
        Sequence $curationLabels = null,
        Image $image = null
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->status = $status;
        $this->stage = $stage;
        $this->doi = $doi;
        $this->indexContent = $indexContent;
        $this->authorLine = $authorLine;
        $this->titlePrefix = $titlePrefix;
        $this->published = $published;
        $this->reviewedDate = $reviewedDate;
        $this->statusDate = $statusDate;
        $this->volume = $volume;
        $this->elocationId = $elocationId;
        $this->pdf = $pdf;
        $this->subjects = $subjects;
        $this->curationLabels = $curationLabels;
        $this->image = $image;
    }

    final public function getIdentifier(): Identifier
    {
        return Identifier::reviewedPreprint($this->id);
    }

    /**
     * @return string|null
     */
    public function getAuthorLine()
    {
        return $this->authorLine;
    }

    /**
     * @return Sequence|string[]
     */
    public function getCurationLabels(): Sequence
    {
        return $this->curationLabels;
    }

    /**
     * @return string|null
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * @return string|null
     */
    public function getElocationId()
    {
        return $this->elocationId;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getIndexContent()
    {
        return $this->indexContent;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getPublishedDate()
    {
        return $this->published;
    }

    /**
     * @return string|null
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getReviewedDate()
    {
        return $this->reviewedDate;
    }

    /**
     * @return string
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Sequence|Subject[]
     */
    public function getSubjects() : Sequence
    {
        return $this->subjects;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getStatusDate()
    {
        return $this->statusDate;
    }

    /**
     * @return string|null
     */
    public function getTitlePrefix()
    {
        return $this->titlePrefix;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Image|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return int|null
     */
    public function getVolume()
    {
        return $this->volume;
    }

    public function getThumbnail()
    {
        return $this->image;
    }
}
