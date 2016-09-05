<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
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
        $this->content = $content
            ->then(function (array $content) {
                return $this->denormalizeBlocks($content);
            });
        if (null === $subjects) {
            $this->subjects = [];
        } else {
            $this->subjects = $subjects;
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
     * @return Subject[]
     */
    public function getSubjects() : array
    {
        if (is_array($this->subjects)) {
            return $this->subjects;
        }

        return $this->subjects->wait();
    }

    /**
     * @return Block[]
     */
    public function getContent() : array
    {
        return $this->content->wait();
    }
}
