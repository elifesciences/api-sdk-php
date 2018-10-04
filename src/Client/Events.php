<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Event;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Events implements Iterator, Sequence
{
    const VERSION_EVENT = 2;
    const VERSION_EVENT_LIST = 1;

    use Client;

    private $count;
    private $descendingOrder = true;
    private $show = 'all';
    private $eventsClient;
    private $denormalizer;

    public function __construct(EventsClient $eventsClient, DenormalizerInterface $denormalizer)
    {
        $this->events = new ArrayObject();
        $this->eventsClient = $eventsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->eventsClient
            ->getEvent(
                ['Accept' => new MediaType(EventsClient::TYPE_EVENT, self::VERSION_EVENT)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Event::class);
            });
    }

    public function show(string $show) : Events
    {
        $clone = clone $this;

        $clone->show = $show;

        if ($clone->show !== $this->show) {
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
                ['Accept' => new MediaType(EventsClient::TYPE_EVENT_LIST, self::VERSION_EVENT_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->show,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $event) {
                    return $this->denormalizer->denormalize($event, Event::class, null, ['snippet' => true]);
                }, $result['items']);
            })
        );
    }

    public function reverse() : Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }
}
