<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class Interview implements Model, HasContent, HasId, HasIdentifier, HasImpactStatement, HasThumbnail, HasSocialImage, HasPublishedDate, HasUpdatedDate
{
    private $id;
    private $interviewee;
    private $title;
    private $published;
    private $updated;
    private $impactStatement;
    private $thumbnail;
    private $socialImage;
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
        Image $thumbnail = null,
        PromiseInterface $socialImage,
        Sequence $content
    ) {
        $this->id = $id;
        $this->interviewee = $interviewee;
        $this->title = $title;
        $this->published = $published;
        $this->updated = $updated;
        $this->impactStatement = $impactStatement;
        $this->thumbnail = $thumbnail;
        $this->socialImage = $socialImage;
        $this->content = $content;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::interview($this->id);
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

    /**
     * @return string|null
     */
    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    /**
     * @return Image|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @return Image|null
     */
    final public function getSocialImage()
    {
        return $this->socialImage->wait();
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
