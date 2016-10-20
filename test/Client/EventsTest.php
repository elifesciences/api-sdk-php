<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Events;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Event;
use test\eLife\ApiSdk\ApiTestCase;

final class EventsTest extends ApiTestCase
{
    /** @var Events */
    private $events;

    /**
     * @before
     */
    protected function setUpEvents()
    {
        $this->events = (new ApiSdk($this->getHttpClient()))->events();
    }

    /**
     * @test
     */
    public function it_is_a_collection()
    {
        $this->assertInstanceOf(Collection::class, $this->events);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockEventListCall(1, 1, 200);
        $this->mockEventListCall(1, 100, 200);
        $this->mockEventListCall(2, 100, 200);

        foreach ($this->events as $i => $event) {
            $this->assertInstanceOf(Event::class, $event);
            $this->assertSame('event'.$i, $event->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockEventListCall(1, 1, 10);

        $this->assertSame(10, $this->events->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockEventListCall(1, 1, 10);
        $this->mockEventListCall(1, 100, 10);

        $array = $this->events->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $event) {
            $this->assertInstanceOf(Event::class, $event);
            $this->assertSame('event'.($i + 1), $event->getId());
        }
    }

    /**
     * @test
     */
    public function it_gets_an_event()
    {
        $this->mockEventCall(7);

        $event = $this->events->get('event7')->wait();

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('event7', $event->getId());

        $this->assertInstanceOf(Paragraph::class, $event->getContent()->toArray()[0]);
        $this->assertSame('Event 7 text', $event->getContent()->toArray()[0]->getText());
    }

    /**
     * @test
     */
    public function it_reuses_already_known_events()
    {
        $this->mockEventListCall(1, 1, 1);
        $this->mockEventListCall(1, 100, 1);

        $this->events->toArray();

        $event = $this->events->get('event1')->wait();

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('event1', $event->getId());

        $this->mockEventCall(1);

        $this->assertInstanceOf(Paragraph::class, $event->getContent()->toArray()[0]);
        $this->assertSame('Event 1 text', $event->getContent()->toArray()[0]->getText());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_type()
    {
        $this->mockEventListCall(1, 1, 5, true, 'open');
        $this->mockEventListCall(1, 100, 5, true, 'open');

        foreach ($this->events->forType('open') as $i => $event) {
            $this->assertSame('event'.$i, $event->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_type()
    {
        $this->mockEventListCall(1, 1, 10);

        $this->events->count();

        $this->mockEventListCall(1, 1, 10, true, 'open');

        $this->assertSame(10, $this->events->forType('open')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_type()
    {
        $this->mockEventListCall(1, 1, 200);
        $this->mockEventListCall(1, 100, 200);
        $this->mockEventListCall(2, 100, 200);

        $this->events->toArray();

        $this->mockEventListCall(1, 1, 200, true, 'open');
        $this->mockEventListCall(1, 100, 200, true, 'open');
        $this->mockEventListCall(2, 100, 200, true, 'open');

        $this->events->forType('open')->toArray();
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockEventListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->events->slice($offset, $length) as $i => $event) {
            $this->assertInstanceOf(Event::class, $event);
            $this->assertSame('event'.($expected[$i]), $event->getId());
        }
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [
                1,
                1,
                [2],
                [
                    ['page' => 2, 'per-page' => 1],
                ],
            ],
            'offset -2, no length' => [
                -2,
                null,
                [4, 5],
                [
                    ['page' => 1, 'per-page' => 1],
                    ['page' => 1, 'per-page' => 100],
                ],
            ],
            'offset 6, no length' => [
                6,
                null,
                [],
                [
                    ['page' => 1, 'per-page' => 1],
                    ['page' => 1, 'per-page' => 100],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockEventListCall(1, 1, 3);
        $this->mockEventListCall(1, 100, 3);

        $map = function (Event $event) {
            return $event->getId();
        };

        $this->assertSame(['event1', 'event2', 'event3'], $this->events->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockEventListCall(1, 1, 5);
        $this->mockEventListCall(1, 100, 5);

        $filter = function (Event $event) {
            return substr($event->getId(), -1) > 3;
        };

        foreach ($this->events->filter($filter) as $i => $event) {
            $this->assertSame('event'.($i + 4), $event->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockEventListCall(1, 1, 5);
        $this->mockEventListCall(1, 100, 5);

        $reduce = function (int $carry = null, Event $event) {
            return $carry + substr($event->getId(), -1);
        };

        $this->assertSame(115, $this->events->reduce($reduce, 100)->wait());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockEventListCall(1, 1, 5);
        $this->mockEventListCall(1, 100, 5);

        $sort = function (Event $a, Event $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->events->sort($sort) as $i => $event) {
            $this->assertSame('event'.(5 - $i), $event->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockEventListCall(1, 1, 5, false);
        $this->mockEventListCall(1, 100, 5, false);

        foreach ($this->events->reverse() as $i => $event) {
            $this->assertSame('event'.$i, $event->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockEventListCall(1, 1, 10);

        $this->events->count();

        $this->assertSame(10, $this->events->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockEventListCall(1, 1, 200);
        $this->mockEventListCall(1, 100, 200);
        $this->mockEventListCall(2, 100, 200);

        $this->events->toArray();

        $this->mockEventListCall(1, 1, 200, false);
        $this->mockEventListCall(1, 100, 200, false);
        $this->mockEventListCall(2, 100, 200, false);

        $this->events->reverse()->toArray();
    }
}
