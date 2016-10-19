<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;

final class BlogArticle
{
    private $id;
    private $title;
    private $published;
    private $impactStatement;
    private $content;
    private $subjects;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        DateTimeImmutable $published,
        string $impactStatement = null,
        Sequence $content,
        Sequence $subjects
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->published = $published;
        $this->impactStatement = $impactStatement;
        $this->content = $content;
        $this->subjects = $subjects;
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
}
