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
    private $version;
    private $type;
    private $doi;
    private $authorLine;
    private $titlePrefix;
    private $title;
    private $versionDate;
    private $statusDate;
    private $reviewedDate;
    private $volume;
    private $elocationId;
    private $subjects;
    private $authors;
    private $curationLabels;
    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $stage,
        int $version,
        string $type,
        string $doi,
        string $authorLine = null,
        string $titlePrefix = null,
        string $title,
        DateTimeImmutable $reviewedDate = null,
        DateTimeImmutable $versionDate = null,
        DateTimeImmutable $statusDate = null,
        int $volume,
        string $elocationId,
        Sequence $subjects,
        Sequence $curationLabels
    ) {
        $this->id = $id;
        $this->stage = $stage;
        $this->version = $version;
        $this->type = $type;
        $this->doi = $doi;
        $this->authorLine = $authorLine;
        $this->titlePrefix = $titlePrefix;
        $this->title = $title;
        $this->reviewedDate = $reviewedDate;
        $this->versionDate = $versionDate;
        $this->statusDate = $statusDate;
        $this->volume = $volume;
        $this->elocationId = $elocationId;
        $this->subjects = $subjects;
        $this->curationLabels = $curationLabels;
    }

    final public function getIdentifier() : Identifier
    {
        return Identifier::article($this->id);
    }

    final public function getId() : string
    {
        return $this->id;
    }

    final public function getStage() : string
    {
        return $this->stage;
    }

    final public function getVersion() : int
    {
        return $this->version;
    }

    final public function getType() : string
    {
        return $this->type;
    }


    final public function getDoi() : string
    {
        return $this->doi;
    }

    /**
     * @return string|null
     */
    final public function getAuthorLine()
    {
        return $this->authorLine;
    }

    /**
     * @return string|null
     */
    final public function getTitlePrefix()
    {
        return $this->titlePrefix;
    }

    final public function getTitle() : string
    {
        return $this->title;
    }

    final public function getFullTitle() : string
    {
        return implode(': ', array_filter([$this->titlePrefix, $this->title]));
    }

    final public function isPublished() : bool
    {
        return null !== $this->published;
    }

    /**
     * @return DateTimeImmutable|null
     */
    final public function getReviewedDate()
    {
        return $this->reviewedDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    final public function getVersionDate()
    {
        return $this->versionDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    final public function getStatusDate()
    {
        return $this->statusDate;
    }

    final public function getVolume() : int
    {
        return $this->volume;
    }

    final public function getElocationId() : string
    {
        return $this->elocationId;
    }

    /**
     * @return Sequence|Subject[]
     */
    final public function getSubjects() : Sequence
    {
        return $this->subjects;
    }

    final public function getAuthors() : Sequence
    {
        return $this->authors;
    }

    final public function getCurationLabels() : Sequence
    {
        return $this->curationLabels;
    }

    /**
     * TODO: is this okay?
     */
    final public function getPublishedDate()
    {
        return $this->reviewedDate;
    }
}
