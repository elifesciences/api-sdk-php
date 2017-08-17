<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Model;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class JobAdvertTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertInstanceOf(Model::class, $event);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertInstanceOf(HasIdentifier::class, $event);
        $this->assertEquals(Identifier::event('id'), $event->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertInstanceOf(HasId::class, $event);
        $this->assertSame('id', $event->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertSame('title', $event->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new Event('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));
        $withOut = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $event = new Event('id', 'title', null, $published = new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertInstanceOf(HasPublishedDate::class, $event);
        $this->assertEquals($published, $event->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));
        $withOut = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updated, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_start_date()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, $starts = new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertEquals($starts, $event->getStarts());
    }

    /**
     * @test
     */
    public function it_has_an_end_date()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), $ends = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertEquals($ends, $event->getEnds());
    }

    /**
     * @test
     */
    public function it_may_have_a_timezone()
    {
        $with = new Event('id', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
            $timeZone = new DateTimeZone('Europe/London'), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));
        $withOut = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertEquals($timeZone, $with->getTimeZone());
        $this->assertNull($withOut->getTimeZone());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, 'http://www.example.com/',
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));
        $withOut = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertEquals('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new ArraySequence($content));

        $this->assertInstanceOf(HasContent::class, $event);
        $this->assertEquals($content, $event->getContent()->toArray());
    }
}
