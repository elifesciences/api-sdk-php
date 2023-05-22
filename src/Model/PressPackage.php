<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class PressPackage implements Model, HasContent, HasId, HasIdentifier, HasImpactStatement, HasSocialImage, HasPublishedDate, HasSubjects, HasUpdatedDate
{
    private $id;
    private $title;
    private $published;
    private $updated;
    private $impactStatement;
    private $socialImage;
    private $subjects;
    private $content;
    private $relatedContent;
    private $mediaContacts;
    private $about;

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
        Sequence $subjects,
        Sequence $content,
        Sequence $relatedContent,
        Sequence $mediaContacts,
        Sequence $about
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->published = $published;
        $this->updated = $updated;
        $this->impactStatement = $impactStatement;
        $this->socialImage = $socialImage;
        $this->subjects = $subjects;
        $this->content = $content;
        $this->relatedContent = $relatedContent;
        $this->mediaContacts = $mediaContacts;
        $this->about = $about;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::pressPackage($this->id);
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getPublishedDate() : DateTimeImmutable
    {
        return $this->published;
    }

    public function getUpdatedDate()
    {
        return $this->updated;
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

    /**
     * @return Sequence|MediaContact[]
     */
    public function getMediaContacts() : Sequence
    {
        return $this->mediaContacts;
    }

    /**
     * @return Sequence|ArticleVersion[]
     */
    public function getAbout() : Sequence
    {
        return $this->about;
    }
}
