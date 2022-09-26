<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

class ReviewedPreprint implements Model, HasId, HasIdentifier, HasDoi, HasPublishedDate, HasReviewedDate, HasThumbnail, HasPdf, HasSubjects, HasCurationLabels
{
    const STAGE_PREVIEW = 'preview';
    const STAGE_PUBLISHED = 'published';

    private $id;
    private $stage;
    private $doi;
    private $authorLine;
    private $titlePrefix;
    private $title;
    private $published;
    private $statusDate;
    private $reviewedDate;
    private $status;
    private $volume;
    private $elocationId;
    private $pdf;
    private $subjects;
    private $curationLabels;
    private $thumbnail;
    private $indexContent;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $stage,
        string $doi = null,
        string $authorLine = null,
        string $titlePrefix = null,
        string $title,
        DateTimeImmutable $published = null,
        DateTimeImmutable $statusDate = null,
        DateTimeImmutable $reviewedDate = null,
        string $status,
        int $volume = null,
        string $elocationId = null,
        string $pdf = null,
        Sequence $subjects,
        array $curationLabels,
        Image $thumbnail = null,
        PromiseInterface $indexContent
    )
    {
        $this->id = $id;
        $this->stage = $stage;
        $this->doi = $doi;
        $this->authorLine = $authorLine;
        $this->titlePrefix = $titlePrefix;
        $this->title = $title;
        $this->published = $published;
        $this->statusDate = $statusDate;
        $this->reviewedDate = $reviewedDate;
        $this->status = $status;
        $this->volume = $volume;
        $this->elocationId = $elocationId;
        $this->pdf = $pdf;
        $this->subjects = $subjects;
        $this->curationLabels = $curationLabels;
        $this->thumbnail = $thumbnail;
        $this->indexContent = $indexContent;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::reviewedPreprint($this->id);
    }

    public function getStage() : string
    {
        return $this->stage;
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
    public function getAuthorLine()
    {
        return $this->authorLine;
    }

    /**
     * @return string|null
     */
    public function getTitlePrefix()
    {
        return $this->titlePrefix;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getPublishedDate()
    {
        return $this->published;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getStatusDate()
    {
        return $this->statusDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getReviewedDate()
    {
        return $this->reviewedDate;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * @return int|null
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @return string|null
     */
    public function getElocationId()
    {
        return $this->elocationId;
    }

    /**
     * @return string|null
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * @return Sequence|Subject[]
     */
    public function getSubjects() : Sequence
    {
        return $this->subjects;
    }

    /**
     * @return string[]
     */
    public function getCurationLabels() : array
    {
        return $this->curationLabels;
    }

    /**
     * @return Image|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @return string|null
     */
    public function getIndexContent()
    {
        return $this->indexContent->wait();
    }
}
