<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use GuzzleHttp\Promise\PromiseInterface;

final class BlogArticle
{
    use HasBlocks;

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
        PromiseInterface $content,
        PromiseInterface $subjects = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->published = $published;
        $this->impactStatement = $impactStatement;
        $this->content = new PromiseCollection($content
            ->then(function (array $content) {
                return $this->denormalizeBlocks($content);
            }));
        if (null === $subjects) {
            $this->subjects = [];
        } else {
            $this->subjects = new PromiseCollection($subjects);
        }
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

    public function hasSubjects() : bool
    {
        return !empty($this->subjects);
    }

    /**
     * @return Collection|Subject[]
     */
    public function getSubjects() : Collection
    {
        if (is_array($this->subjects)) {
            return new ArrayCollection($this->subjects);
        }

        return $this->subjects;
    }

    /**
     * @return Collection|Block[]
     */
    public function getContent() : Collection
    {
        return $this->content;
    }
}
