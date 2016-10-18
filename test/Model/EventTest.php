<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Place;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class EventTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));

        $this->assertSame('id', $event->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));

        $this->assertSame('title', $event->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new Event('id', 'title', 'impact statement', new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));
        $withOut = new Event('id', 'title', null, new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_start_date()
    {
        $event = new Event('id', 'title', null, $starts = new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));

        $this->assertEquals($starts, $event->getStarts());
    }

    /**
     * @test
     */
    public function it_has_an_end_date()
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable(), $ends = new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));

        $this->assertEquals($ends, $event->getEnds());
    }

    /**
     * @test
     */
    public function it_may_have_a_timezone()
    {
        $with = new Event('id', 'title', 'impact statement', new DateTimeImmutable(), new DateTimeImmutable(),
            $timeZone = new DateTimeZone('Europe/London'),
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));
        $withOut = new Event('id', 'title', null, new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));

        $this->assertEquals($timeZone, $with->getTimeZone());
        $this->assertNull($withOut->getTimeZone());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $event = new Event('id', 'title', null, new DateTimeImmutable(), new DateTimeImmutable(), null,
            new ArraySequence($content),
            rejection_for('Event venue should not be unwrapped'));

        $this->assertEquals($content, $event->getContent()->toArray());
    }

    /**
     * @test
     */
    public function it_may_have_a_venue()
    {
        $venue = new Place(null, null, ['foo']);

        $with = new Event('id', 'title', null, new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')), promise_for($venue));
        $withOut = new Event('id', 'title', null, new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

        $this->assertTrue($with->hasVenue());
        $this->assertEquals($venue, $with->getVenue()->wait());
        $this->assertFalse($withOut->hasVenue());
        $this->assertNull($withOut->getVenue()->wait());
    }
}
