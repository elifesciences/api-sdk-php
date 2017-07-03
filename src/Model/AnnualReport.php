<?php

namespace eLife\ApiSdk\Model;

final class AnnualReport implements Model, HasImpactStatement, HasPdf
{
    private $year;
    private $uri;
    private $pdf;
    private $title;
    private $impactStatement;
    private $image;

    public function __construct(int $year, string $uri, string $pdf = null, string $title, string $impactStatement = null, Image $image)
    {
        $this->year = $year;
        $this->uri = $uri;
        $this->pdf = $pdf;
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

    /**
     * @return string|null
     */
    public function getPdf()
    {
        return $this->pdf;
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
