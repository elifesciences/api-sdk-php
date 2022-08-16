<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

class ReviewedPreprint implements Article, HasDoi, HasIdentifier, HasSubjects, HasPublishedDate
{
    const STAGE_PREVIEW = 'preview';
    const STAGE_PUBLISHED = 'published';

    private $id;
    private $stage;
    private $type;
    private $doi;
    private $authorLine;
    private $titlePrefix;
    private $title;
    private $statusDate;
    private $reviewedDate;
    private $volume;
    private $elocationId;
    private $subjects;
    private $curationLabels;
    private $status;
    private $published;
    private $pdf;
    private $image;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        string $status,
        string $stage,
        string $doi = null,
        string $authorLine = null,
        string $titlePrefix = null,
        DateTimeImmutable $published = null,
        DateTimeImmutable $reviewedDate = null,
        DateTimeImmutable $statusDate = null,
        int $volume = null,
        string $elocationId = null,
        string $pdf = null,
        string $type = null,
        Sequence $subjects = null,
        Sequence $curationLabels = null,
        Sequence $image = null
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->status = $status;
        $this->stage = $stage;
        $this->doi = $doi;
        $this->authorLine = $authorLine;
        $this->titlePrefix = $titlePrefix;
        $this->published = $published;
        $this->reviewedDate = $reviewedDate;
        $this->statusDate = $statusDate;
        $this->volume = $volume;
        $this->elocationId = $elocationId;
        $this->pdf = $pdf;
        $this->type = $type;
        $this->subjects = $subjects;
        $this->curationLabels = $curationLabels;
        $this->image = $image;
    }

    final public function getIdentifier(): Identifier
    {
        return Identifier::article($this->id);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return $this->stage;
    }

    /**
     * @return string|null
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getDoi(): string
    {
        return $this->doi;
    }

    /**
     * @return string|null
     */
    public function getAuthorLine(): string
    {
        return $this->authorLine;
    }

    /**
     * @return string|null
     */
    public function getTitlePrefix(): string
    {
        return $this->titlePrefix;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getStatusDate(): DateTimeImmutable
    {
        return $this->statusDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getReviewedDate(): DateTimeImmutable
    {
        return $this->reviewedDate;
    }

    /**
     * @return int|null
     */
    public function getVolume(): int
    {
        return $this->volume;
    }

    /**
     * @return string|null
     */
    public function getElocationId(): string
    {
        return $this->elocationId;
    }

    /**
     * @return Sequence|null
     */
    public function getSubjects(): Sequence
    {
        return $this->subjects;
    }

    /**
     * @return Sequence|null
     */
    public function getCurationLabels(): Sequence
    {
        return $this->curationLabels;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getPublishedDate(): DateTimeImmutable
    {
        return $this->published;
    }

    /**
     * @return string|null
     */
    public function getPdf(): string
    {
        return $this->pdf;
    }

    /**
     * @return Sequence|null
     */
    public function getImage(): Sequence
    {
        return $this->image;
    }
}
