<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class PodcastEpisodeChapter implements HasContent, HasImpactStatement
{
    private $number;
    private $title;
    private $longTitle;
    private $time;
    private $impactStatement;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        int $number,
        string $title,
        string $longTitle = null,
        int $time,
        string $impactStatement = null,
        Sequence $content
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->longTitle = $longTitle;
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

    /**
     * @return string|null
     */
    public function getLongTitle()
    {
        return $this->longTitle;
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

    /**
     * @return Sequence|Model[]
     */
    public function getContent(): Sequence
    {
        return $this->content;
    }
}
