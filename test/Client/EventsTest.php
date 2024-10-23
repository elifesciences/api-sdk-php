<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiSdk\ApiClient\EventsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Events;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Event;
use test\eLife\ApiSdk\ApiTestCase;

final class EventsTest extends ApiTestCase
{
    use SlicingTestCase;

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
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->events);
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

        $this->assertFalse($this->events->isEmpty());
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
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockEventListCall(1, 1, 1);

        $this->assertTrue(isset($this->events[0]));
        $this->assertSame('event1', $this->events[0]->getId());

        $this->mockNotFound(
            'events?page=6&per-page=1&show=all&order=desc',
            ['Accept' => (string) new MediaType(EventsClient::TYPE_EVENT_LIST, 1)]
        );

        $this->assertFalse(isset($this->events[5]));
        $this->assertSame(null, $this->events[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->events[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_an_event()
    {
        $this->mockEventCall('event7');

        $event = $this->events->get('event7')->wait();

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('event7', $event->getId());

        $this->assertInstanceOf(Paragraph::class, $event->getContent()[0]);
        $this->assertSame('Event event7 text', $event->getContent()[0]->getText());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_open_and_closed()
    {
        $this->mockEventListCall(1, 1, 5, true, 'open');
        $this->mockEventListCall(1, 100, 5, true, 'open');

        foreach ($this->events->show('open') as $i => $event) {
            $this->assertSame('event'.$i, $event->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_open_and_closed()
    {
        $this->mockEventListCall(1, 1, 10);

        $this->events->count();

        $this->mockEventListCall(1, 1, 10, true, 'open');

        $this->assertSame(10, $this->events->show('open')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_open_and_closed()
    {
        $this->mockEventListCall(1, 1, 200);
        $this->mockEventListCall(1, 100, 200);
        $this->mockEventListCall(2, 100, 200);

        $this->events->toArray();

        $this->mockEventListCall(1, 1, 200, true, 'open');
        $this->mockEventListCall(1, 100, 200, true, 'open');
        $this->mockEventListCall(2, 100, 200, true, 'open');

        $this->events->show('open')->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockEventListCall(1, 1, 5);
        $this->mockEventListCall(1, 100, 5);

        $values = $this->events->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'event1', 'event2', 'event3', 'event4', 'event5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockEventListCall(1, 1, 5);
        $this->mockEventListCall(1, 100, 5);

        $values = $this->events->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['event1', 'event2', 'event3', 'event4', 'event5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockEventListCall(1, 1, 5);
        $this->mockEventListCall(1, 100, 5);

        $values = $this->events->drop(2)->map($this->tidyValue());

        $this->assertSame(['event1', 'event2', 'event4', 'event5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockEventListCall(1, 1, 5);
        $this->mockEventListCall(1, 100, 5);

        $values = $this->events->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['event1', 'event2', 2, 'event3', 'event4', 'event5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockEventListCall(1, 1, 5);
        $this->mockEventListCall(1, 100, 5);

        $values = $this->events->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['event1', 'event2', 2, 'event4', 'event5'], $values->toArray());
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

        $this->assertSame(115, $this->events->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->events, $this->events->flatten());
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
