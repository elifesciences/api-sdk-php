<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class PodcastEpisodeChapter
{
    private $number;
    private $title;
    private $time;
    private $impactStatement;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        int $number,
        string $title,
        int $time,
        string $impactStatement = null,
        Sequence $content
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->time = $time;
        $this->impactStatement = $impactStatement;
        $this->content = $content;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return string|null
     */
    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    public function getContent(): Sequence
    {
        return $this->content;
    }
}
