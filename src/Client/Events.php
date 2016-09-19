<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Promise\CallbackPromise;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Events implements Iterator, Collection
{
    use ArrayFromIterator;
    use SlicedIterator;

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

    public function slice(int $offset, int $length = null) : Collection
    {
        if (null === $length) {
            return new PromiseCollection($this->all()
                ->then(function (Collection $collection) use ($offset) {
                    return $collection->slice($offset);
                })
            );
        }

        return new PromiseCollection($this->eventsClient
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

                $fullPromise = new CallbackPromise(function () use ($result) {
                    $promises = [];
                    foreach ($result['items'] as $event) {
                        $promises[$event['id']] = $this->eventsClient->getEvent(
                            ['Accept' => new MediaType(EventsClient::TYPE_EVENT, 1)],
                            $event['id']
                        );
                    }

                    return $promises;
                });

                foreach ($result['items'] as $event) {
                    if (isset($this->events[$event['id']])) {
                        $events[] = $this->events[$event['id']]->wait();
                    } else {
                        $event['content'] = $fullPromise
                            ->then(function (array $promises) use ($event) {
                                return $promises[$event['id']]->wait()['content'];
                            });

                        $events[] = $event = $this->denormalizer->denormalize($event, Event::class);
                        $this->events[$event->getId()] = promise_for($event);
                    }
                }

                return new ArrayCollection($events);
            })
        );
    }

    public function reverse() : Collection
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
