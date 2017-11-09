<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Model;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EventNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(EventsClient $eventsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $event) : string {
                return $event['id'];
            },
            function (string $id) use ($eventsClient) : PromiseInterface {
                return $eventsClient->getEvent(
                    ['Accept' => new MediaType(EventsClient::TYPE_EVENT, 2)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Event
    {
        if (!empty($context['snippet'])) {
            $event = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($event
                ->then(function (Result $event) {
                    return $event['content'];
                }));
        } else {
            $data['content'] = new ArraySequence($data['content'] ?? []);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        return new Event(
            $data['id'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['starts']),
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['ends']),
            !empty($data['timezone']) ? new DateTimeZone($data['timezone']) : null,
            $data['uri'] ?? null,
            $data['content']
        );
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
            'published' => $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
            'starts' => $object->getStarts()->format(ApiSdk::DATE_FORMAT),
            'ends' => $object->getEnds()->format(ApiSdk::DATE_FORMAT),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'event';
        }

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if ($object->getTimeZone()) {
            $data['timezone'] = $object->getTimeZone()->getName();
        }

        if ($object->getUri()) {
            $data['uri'] = $object->getUri();
        }

        if (empty($context['snippet']) && $object->getContent()->notEmpty()) {
            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Event;
    }
}
