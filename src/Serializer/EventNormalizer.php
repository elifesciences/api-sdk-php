<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class EventNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $eventsClient;
    private $found = [];
    private $globalCallback;

    public function __construct(EventsClient $eventsClient)
    {
        $this->eventsClient = $eventsClient;
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Event
    {
        if (!empty($context['snippet'])) {
            $event = $this->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($event
                ->then(function (Result $event) {
                    return $event['content'];
                }));

            $data['venue'] = $event
                ->then(function (Result $event) {
                    return $event['venue'] ?? null;
                });
        } else {
            $data['content'] = new ArraySequence($data['content']);

            $data['venue'] = promise_for($data['venue'] ?? null);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['venue'] = $data['venue']
            ->then(function (array $venue = null) use ($format, $context) {
                if (null === $venue) {
                    return null;
                }

                return $this->denormalizer->denormalize($venue, Place::class, $format, $context);
            });

        return new Event(
            $data['id'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $data['starts']),
            DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $data['ends']),
            !empty($data['timezone']) ? new DateTimeZone($data['timezone']) : null,
            $data['content'],
            $data['venue']
        );
    }

    private function denormalizeSnippet(array $event) : PromiseInterface
    {
        if (isset($this->found[$event['id']])) {
            return $this->found[$event['id']];
        }

        $this->found[$event['id']] = null;

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                foreach ($this->found as $id => $event) {
                    if (null === $event) {
                        $this->found[$id] = $this->eventsClient->getEvent(
                            ['Accept' => new MediaType(EventsClient::TYPE_EVENT, 1)],
                            $id
                        );
                    }
                }

                $this->globalCallback = null;

                return all($this->found)->wait();
            });
        }

        return $this->globalCallback
            ->then(function (array $events) use ($event) {
                return $events[$event['id']];
            });
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            Event::class === $type
            ||
            Model::class === $type && 'event' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param Event $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'starts' => $object->getStarts()->format('Y-m-d\TH:i:s\Z'),
            'ends' => $object->getStarts()->format('Y-m-d\TH:i:s\Z'),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'event';
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if ($object->getTimeZone()) {
            $data['timezone'] = $object->getTimeZone()->getName();
        }

        if (empty($context['snippet'])) {
            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();

            if ($venue = $object->getVenue()) {
                $data['venue'] = $this->normalizer->normalize($venue, $format, $context);
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Event;
    }
}
