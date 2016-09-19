<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class Event
{
    private $id;
    private $title;
    private $impactStatement;
    private $starts;
    private $ends;
    private $timeZone;
    private $content;
    private $venue;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        string $impactStatement = null,
        DateTimeImmutable $starts,
        DateTimeImmutable $ends,
        DateTimeZone $timeZone = null,
        Collection $content,
        PromiseInterface $venue = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->starts = $starts;
        $this->ends = $ends;
        $this->timeZone = $timeZone;
        $this->content = $content;
        $this->venue = $venue;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
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

    public function getStarts(): DateTimeImmutable
    {
        return $this->starts;
    }

    public function getEnds(): DateTimeImmutable
    {
        return $this->ends;
    }

    /**
     * @return DateTimeZone|null
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    public function getContent(): Collection
    {
        return $this->content;
    }

    public function hasVenue(): bool
    {
        return null !== $this->venue;
    }

    public function getVenue(): PromiseInterface
    {
        return promise_for($this->venue);
    }
}
