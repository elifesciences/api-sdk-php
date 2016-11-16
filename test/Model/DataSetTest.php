<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use PHPUnit_Framework_TestCase;

final class DataSetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $dataSet = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        $this->assertSame('id', $dataSet->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $dataSet = new DataSet('id', $date = new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        $this->assertEquals($date, $dataSet->getDate());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $dataSet = new DataSet('id', new Date(2000), $authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        $this->assertEquals($authors, $dataSet->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_more_authors()
    {
        $dataSet = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title', null, null, null, 'http://www.example.com/');

        $this->assertTrue($dataSet->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $dataSet = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        $this->assertSame('title', $dataSet->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_data_id()
    {
        $with = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', 'data id', null, null, 'http://www.example.com/');
        $withOut = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        $this->assertSame('data id', $with->getDataId());
        $this->assertNull($withOut->getDataId());
    }

    /**
     * @test
     */
    public function it_may_have_details()
    {
        $with = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, 'details', null, 'http://www.example.com/');
        $withOut = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        $this->assertSame('details', $with->getDetails());
        $this->assertNull($withOut->getDetails());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, '10.1000/182', 'http://www.example.com/');
        $withOut = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $dataSet = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        $this->assertSame('http://www.example.com/', $dataSet->getUri());
    }
}
