<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPdf;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class AnnualReportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', null);

        $this->assertInstanceOf(HasIdentifier::class, $annualReport);
        $this->assertEquals(Identifier::annualReport(2012), $annualReport->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_a_year()
    {
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', null);

        $this->assertSame(2012, $annualReport->getYear());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', null);

        $this->assertSame('http://www.example.com/2012', $annualReport->getUri());
    }

    /**
     * @test
     */
    public function it_may_have_a_pdf()
    {
        $with = new AnnualReport(2012, 'http://www.example.com/2012', 'http://www.example.com/2012/assets/annual-report-2012.pdf', 'title', null);
        $withOut = new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', null);

        $this->assertInstanceOf(HasPdf::class, $with);
        $this->assertSame('http://www.example.com/2012/assets/annual-report-2012.pdf', $with->getPdf());
        $this->assertNull($withOut->getPdf());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', null);

        $this->assertSame('title', $annualReport->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', 'impact statement');
        $withOut = new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', null);

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }
}
