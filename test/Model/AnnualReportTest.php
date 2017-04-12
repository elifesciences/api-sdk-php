<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\Image;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class AnnualReportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $image = Builder::for(Image::class)->sample('thumbnail');
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertSame(2012, $annualReport->getYear());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $image = Builder::for(Image::class)->sample('thumbnail');
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertSame('http://www.example.com/2012', $annualReport->getUri());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $image = Builder::for(Image::class)->sample('thumbnail');
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertSame('title', $annualReport->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $image = Builder::for(Image::class)->sample('thumbnail');
        $with = new AnnualReport(2012, 'http://www.example.com/2012', 'title', 'impact statement', $image);
        $withOut = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $image = Builder::for(Image::class)->sample('thumbnail');
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', 'title', null, $image);

        $this->assertEquals($image, $annualReport->getImage());
    }
}
