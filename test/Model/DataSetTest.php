<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class DataSetTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $dataSet = Builder::for(DataSet::class)
            ->withId('id')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $dataSet);
        $this->assertSame('id', $dataSet->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $dataSet = Builder::for(DataSet::class)
            ->withDate($date = new Date(2000))
            ->__invoke();

        $this->assertEquals($date, $dataSet->getDate());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $dataSet = Builder::for(DataSet::class)
            ->withAuthors($authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))])
            ->__invoke();

        $this->assertEquals($authors, $dataSet->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_more_authors()
    {
        $dataSet = Builder::for(DataSet::class)
            ->withAuthorsEtAl(true)
            ->__invoke();

        $this->assertTrue($dataSet->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $dataSet = Builder::for(DataSet::class)
            ->withTitle('title')
            ->__invoke();

        $this->assertSame('title', $dataSet->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_data_id()
    {
        $with = Builder::for(DataSet::class)
            ->withDataId('data id')
            ->__invoke();
        $withOut = Builder::for(DataSet::class)
            ->withDataId(null)
            ->__invoke();

        $this->assertSame('data id', $with->getDataId());
        $this->assertNull($withOut->getDataId());
    }

    /**
     * @test
     */
    public function it_may_have_details()
    {
        $with = Builder::for(DataSet::class)
            ->withDetails('details')
            ->__invoke();
        $withOut = Builder::for(DataSet::class)
            ->withDetails(null)
            ->__invoke();

        $this->assertSame('details', $with->getDetails());
        $this->assertNull($withOut->getDetails());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = Builder::for(DataSet::class)
            ->withDoi('10.1000/182')
            ->__invoke();
        $withOut = Builder::for(DataSet::class)
            ->withDoi(null)
            ->__invoke();

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $with = Builder::for(DataSet::class)
            ->withUri('http://www.example.com/')
            ->__invoke();
        $withOut = Builder::for(DataSet::class)
            ->withUri(null)
            ->__invoke();

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
