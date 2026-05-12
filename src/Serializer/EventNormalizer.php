<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiClient\EventsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Events;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class EventNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(EventsClient $eventsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $event) : string {
                return $event['id'];
            },
            function (string $id) use ($eventsClient) : PromiseInterface {
                return $eventsClient->getEvent(
                    ['Accept' => (string) new MediaType(EventsClient::TYPE_EVENT, Events::VERSION_EVENT)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : Event
    {
        if (!empty($context['snippet'])) {
            $event = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($event
                ->then(function (Result $event) {
                    return $event['content'];
                }));

            $data['image']['social'] = $event
                ->then(function (Result $event) {
                    return $event['image']['social'] ?? null;
                });
        } else {
            $data['content'] = new ArraySequence($data['content'] ?? []);
            $data['image']['social'] = promise_for($data['image']['social'] ?? null);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['image']['social'] = $data['image']['social']
            ->then(function ($socialImage) use ($format, $context) {
                return false === empty($socialImage) ? $this->denormalizer->denormalize($socialImage, Image::class, $format, $context) : null;
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
            $data['image']['social'],
            $data['content']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Event::class === $type
            ||
            Model::class === $type && 'event' === ($data['type'] ?? 'unknown');
    }


    /**
     * @param $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'published' => $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
            'starts' => $data->getStarts()->format(ApiSdk::DATE_FORMAT),
            'ends' => $data->getEnds()->format(ApiSdk::DATE_FORMAT),
        ];

        if (!empty($context['type'])) {
            $arr['type'] = 'event';
        }

        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }

        if ($data->getTimeZone()) {
            $arr['timezone'] = $data->getTimeZone()->getName();
        }

        if ($data->getUri()) {
            $arr['uri'] = $data->getUri();
        }

        if (empty($context['snippet'])) {
            if ($data->getContent()->notEmpty()) {
                $arr['content'] = $data->getContent()->map(function (Block $block) use ($format, $context) {
                    return $this->normalizer->normalize($block, $format, $context);
                })->toArray();
            }

            if ($data->getSocialImage()) {
                $arr['image']['social'] = $this->normalizer->normalize($data->getSocialImage(), $format, $context);
            }
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Event;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Event::class => false,
            Model::class => false,
        ];
    }
}
