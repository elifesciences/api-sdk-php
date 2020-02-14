<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\JobAdvert;
use eLife\ApiSdk\Model\Model;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit_Framework_TestCase;

final class JobAdvertTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
            new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(Model::class, $jobAdvert);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasIdentifier::class, $jobAdvert);
        $this->assertEquals(Identifier::jobAdvert('id'), $jobAdvert->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasId::class, $jobAdvert);
        $this->assertSame('id', $jobAdvert->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));

        $this->assertSame('title', $jobAdvert->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
          new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));
        $withOut = new JobAdvert('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
          new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', $published = new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
          new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasPublishedDate::class, $jobAdvert);
        $this->assertEquals($published, $jobAdvert->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')),
          new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));
        $withOut = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null,
          new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updated, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_closing_date()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), $closing = new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new PromiseSequence(rejection_for('Job advert content should not be unwrapped')));

        $this->assertEquals($closing, $jobAdvert->getClosingDate());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', $closing = new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new ArraySequence($content));

        $this->assertInstanceOf(HasContent::class, $jobAdvert);
        $this->assertEquals($content, $jobAdvert->getContent()->toArray());
    }
}
