<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\DataReference;
use PHPUnit\Framework\TestCase;

final class DataReferenceTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reference = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertInstanceOf(HasId::class, $reference);
        $this->assertSame('id', $reference->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new DataReference('id', $date = new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_discriminator()
    {
        $with = new DataReference('id', new Date(2000), 'a',
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');
        $withOut = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    /**
     * @test
     */
    public function it_may_have_authors()
    {
        $with = new DataReference('id', new Date(2000), null,
            $authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [],
            false,
            'title', 'source');
        $withOut = new DataReference('id', new Date(2000), null, [], false,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'source');

        $this->assertEquals($authors, $with->getAuthors());
        $this->assertEmpty($withOut->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, [], false, [], false, 'title',
            'source');
        $withOut = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_may_have_compilers()
    {
        $with = new DataReference('id', new Date(2000), null, [], false,
            $compilers = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false,
            'title',
            'source');
        $withOut = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertEquals($compilers, $with->getCompilers());
        $this->assertEmpty($withOut->getCompilers());
    }

    /**
     * @test
     */
    public function it_may_have_further_compilers()
    {
        $with = new DataReference('id', new Date(2000), null, [], false,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, [], false, 'title', 'source');
        $withOut = new DataReference('id', new Date(2000), null, [], false,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'source');

        $this->assertTrue($with->compilersEtAl());
        $this->assertFalse($withOut->compilersEtAl());
    }

    /**
     * @test
     */
    public function it_may_have_curators()
    {
        $with = new DataReference('id', new Date(2000), null, [], false, [], false,
            $curators = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'source');
        $withOut = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertEquals($curators, $with->getCurators());
        $this->assertEmpty($withOut->getCurators());
    }

    /**
     * @test
     */
    public function it_may_have_further_curators()
    {
        $with = new DataReference('id', new Date(2000), null, [], false, [], false,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title', 'source');
        $withOut = new DataReference('id', new Date(2000), null, [], false, [], false,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', 'source');

        $this->assertTrue($with->curatorsEtAl());
        $this->assertFalse($withOut->curatorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_source()
    {
        $reference = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertSame('source', $reference->getSource());
    }

    /**
     * @test
     */
    public function it_may_have_a_data_id()
    {
        $with = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source', 'data id', null, '10.1000/182');
        $withOut = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertSame('data id', $with->getDataId());
        $this->assertNull($withOut->getDataId());
    }

    /**
     * @test
     */
    public function it_may_have_an_assigning_authority()
    {
        $with = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source', null, $assigningAuthority = new Place(['assigning authority']),
            '10.1000/182');
        $withOut = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertEquals($assigningAuthority, $with->getAssigningAuthority());
        $this->assertNull($withOut->getDataId());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source', null, null, null, '10.1000/182');
        $withOut = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source', null, null, null, null, 'http://www.example.com/');
        $withOut = new DataReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
