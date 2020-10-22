<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class JobAdvert implements Model, HasContent, HasId, HasIdentifier, HasImpactStatement, HasSocialImage, HasPublishedDate, HasUpdatedDate
{
    private $id;
    private $title;
    private $impactStatement;
    private $socialImage;
    private $closingDate;
    private $publishedDate;
    private $updatedDate;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        string $impactStatement = null,
        PromiseInterface $socialImage,
        DateTimeImmutable $publishedDate,
        DateTimeImmutable $closingDate,
        DateTimeImmutable $updatedDate = null,
        Sequence $content
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->socialImage = $socialImage;
        $this->publishedDate = $publishedDate;
        $this->updatedDate = $updatedDate;
        $this->closingDate = $closingDate;
        $this->content = $content;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::jobAdvert($this->id);
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

    /**
     * @return Image|null
     */
    public function getSocialImage()
    {
        return $this->socialImage->wait();
    }

    public function getPublishedDate() : DateTimeImmutable
    {
        return $this->publishedDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    public function getClosingDate() : DateTimeImmutable
    {
        return $this->closingDate;
    }

    public function getContent() : Sequence
    {
        return $this->content;
    }
}
