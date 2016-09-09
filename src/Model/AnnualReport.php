<?php

namespace eLife\ApiSdk\Model;

final class AnnualReport
{
    private $year;
    private $uri;
    private $title;
    private $impactStatement;
    private $image;

    public function __construct(int $year, string $uri, string $title, string $impactStatement = null, Image $image)
    {
        $this->year = $year;
        $this->uri = $uri;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->image = $image;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getUri(): string
    {
        return $this->uri;
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

    public function getImage(): Image
    {
        return $this->image;
    }
}
