<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use PHPUnit_Framework_TestCase;

final class AnnualReportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertSame(2012, $annualReport->getYear());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertSame('http://www.example.com/2012', $annualReport->getUri());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertSame('title', $annualReport->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $with = new AnnualReport(2012, 'http://www.example.com/2012', 'title', 'impact statement', $image);
        $withOut = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertEquals($image, $annualReport->getImage());
    }
}
