<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;

final class Interview implements Model, HasContent, HasId, HasImpactStatement, HasPublishedDate, HasUpdatedDate
{
    private $id;
    private $interviewee;
    private $title;
    private $published;
    private $updated;
    private $impactStatement;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        Interviewee $interviewee,
        string $title,
        DateTimeImmutable $published,
        DateTimeImmutable $updated = null,
        string $impactStatement = null,
        Sequence $content
    ) {
        $this->id = $id;
        $this->interviewee = $interviewee;
        $this->title = $title;
        $this->published = $published;
        $this->updated = $updated;
        $this->impactStatement = $impactStatement;
        $this->content = $content;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getInterviewee() : Interviewee
    {
        return $this->interviewee;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getSubTitle() : String
    {
        return 'An interview with '.$this->interviewee->getPerson()->getPreferredName();
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

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate()
    {
        return $this->updated;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }
}
