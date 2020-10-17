<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class BlogArticle implements Model, HasContent, HasId, HasIdentifier, HasImpactStatement, HasPublishedDate, HasSocialImage, HasSubjects, HasUpdatedDate
{
    private $id;
    private $title;
    private $published;
    private $updated;
    private $impactStatement;
    private $socialImage;
    private $content;
    private $subjects;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        DateTimeImmutable $published,
        DateTimeImmutable $updated = null,
        string $impactStatement = null,
        PromiseInterface $socialImage,
        Sequence $content,
        Sequence $subjects
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->published = $published;
        $this->updated = $updated;
        $this->impactStatement = $impactStatement;
        $this->socialImage = $socialImage;
        $this->content = $content;
        $this->subjects = $subjects;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::blogArticle($this->id);
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

    /**
     * @return Image|null
     */
    public function getSocialImage()
    {
        return $this->socialImage->wait();
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
