<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;

final class Digest implements Model, HasContent, HasId, HasIdentifier, HasImpactStatement, HasPublishedDate, HasSubjects, HasUpdatedDate
{
    private $id;
    private $title;
    private $impactStatement;
    private $published;
    private $updated;
    private $thumbnail;
    private $subjects;
    private $content;
    private $relatedContent;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        string $impactStatement = null,
        DateTimeImmutable $published,
        DateTimeImmutable $updated = null,
        Image $thumbnail,
        Sequence $subjects,
        Sequence $content,
        Sequence $relatedContent
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->published = $published;
        $this->updated = $updated;
        $this->thumbnail = $thumbnail;
        $this->subjects = $subjects;
        $this->content = $content;
        $this->relatedContent = $relatedContent;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::digest($this->id);
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
        return $this->published;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate()
    {
        return $this->updated;
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
     * @return Sequence|Block[]
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
}
