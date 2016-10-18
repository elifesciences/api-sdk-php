<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Place;
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

    public function denormalize($data, $class, $format = null, array $context = []) : Event
    {
        $data['content'] = new PromiseSequence(promise_for($data['content'])
            ->then(function (array $blocks) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $blocks);
            }));

        $data['venue'] = !empty($data['venue']) ? promise_for($data['venue'])
            ->then(function (array $venue = null) use ($format, $context) {
                if (null === $venue) {
                    return null;
                }

                return $this->denormalizer->denormalize($venue, Place::class, $format, $context);
            }) : null;

        return new Event(
            $data['id'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['starts']),
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['ends']),
            !empty($data['timezone']) ? new DateTimeZone($data['timezone']) : null,
            $data['content'],
            $data['venue']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Event::class === $type;
    }

    /**
     * @param Event $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'starts' => $object->getStarts()->format(DATE_ATOM),
            'ends' => $object->getStarts()->format(DATE_ATOM),
        ];

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

            if ($object->hasVenue()) {
                $data['venue'] = $object->getVenue()->then(function (Place $venue) use ($format, $context) {
                    return $this->normalizer->normalize($venue, $format, $context);
                });
            }
        }

        return all($data)->wait();
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Event;
    }
}
