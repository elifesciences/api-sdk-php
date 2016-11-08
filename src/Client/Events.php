<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\SlicedArrayAccess;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Events implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedArrayAccess;
    use SlicedIterator {
        SlicedIterator::getPage insteadof SlicedArrayAccess;
        SlicedIterator::isEmpty insteadof SlicedArrayAccess;
        SlicedIterator::resetPages insteadof SlicedArrayAccess;
    }

    private $count;
    private $events;
    private $descendingOrder = true;
    private $type = 'all';
    private $eventsClient;
    private $denormalizer;

    public function __construct(EventsClient $eventsClient, DenormalizerInterface $denormalizer)
    {
        $this->events = new ArrayObject();
        $this->eventsClient = $eventsClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(string $id) : PromiseInterface
    {
        if (isset($this->events[$id])) {
            return $this->events[$id];
        }

        return $this->events[$id] = $this->eventsClient
            ->getEvent(
                ['Accept' => new MediaType(EventsClient::TYPE_EVENT, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Event::class);
            });
    }

    public function forType(string $type) : Events
    {
        $clone = clone $this;

        $clone->type = $type;

        if ($clone->type !== $this->type) {
            $clone->count = null;
        }

        return $clone;
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        if (null === $length) {
            return new PromiseSequence($this->all()
                ->then(function (Sequence $sequence) use ($offset) {
                    return $sequence->slice($offset);
                })
            );
        }

        return new PromiseSequence($this->eventsClient
            ->listEvents(
                ['Accept' => new MediaType(EventsClient::TYPE_EVENT_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->type,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $events = [];

                foreach ($result['items'] as $event) {
                    if (isset($this->events[$event['id']])) {
                        $events[] = $this->events[$event['id']]->wait();
                    } else {
                        $events[] = $event = $this->denormalizer->denormalize($event, Event::class, null,
                            ['snippet' => true]);
                        $this->events[$event->getId()] = promise_for($event);
                    }
                }

                return new ArraySequence($events);
            })
        );
    }

    public function reverse() : Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }

    public function count() : int
    {
        if (null === $this->count) {
            $this->slice(0, 1)->count();
        }

        return $this->count;
    }
}
