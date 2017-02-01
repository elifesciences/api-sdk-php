<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\EventNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class EventNormalizerTest extends ApiTestCase
{
    /** @var EventNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new EventNormalizer(new EventsClient($this->getHttpClient()));
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
    }

    /**
     * @test
     */
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canNormalizeProvider
     */
    public function it_can_normalize_events($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Event content should not be unwrapped')),
            rejection_for('Event venue should not be unwrapped'));

        return [
            'event' => [$event, null, true],
            'event with format' => [$event, 'foo', true],
            'non-event' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_events(Event $event, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($event, null, $context));
    }

    /**
     * @test
     */
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canDenormalizeProvider
     */
    public function it_can_denormalize_events($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'event' => [[], Event::class, [], true],
            'event by type' => [['type' => 'event'], Model::class, [], true],
            'non-event' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_events(
        Event $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Event::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $starts = new DateTimeImmutable('2017-01-01T14:00:00Z', new DateTimeZone('Z'));
        $ends = new DateTimeImmutable('2017-01-01T16:00:00Z', new DateTimeZone('Z'));
        $timezone = new DateTimeZone('Europe/London');
        $venue = new Place(null, null, ['venue']);

        return [
            'complete' => [
                new Event('id', 'title', 'impact statement', $starts, $ends, $timezone,
                    new ArraySequence([new Paragraph('text')]), promise_for($venue)),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'impact statement',
                    'timezone' => $timezone->getName(),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                    'venue' => ['name' => ['venue']],
                ],
            ],
            'minimum' => [
                new Event('id', 'title', null, $starts, $ends, null, new ArraySequence([new Paragraph('text')]),
                    promise_for(null)),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new Event('event1', 'Event 1 title', 'Event 1 impact statement', $starts, $ends, $timezone,
                    new ArraySequence([new Paragraph('Event 1 text')]), promise_for($venue)),
                ['snippet' => true, 'type' => true],
                [
                    'id' => 'event1',
                    'title' => 'Event 1 title',
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'Event 1 impact statement',
                    'timezone' => $timezone->getName(),
                    'type' => 'event',
                ],
                function (ApiTestCase $test) {
                    $test->mockEventCall(1, true);
                },
            ],
            'minimum snippet' => [
                new Event('event1', 'Event 1 title', null, $starts, $ends, null,
                    new ArraySequence([new Paragraph('Event 1 text')]), promise_for(null)),
                ['snippet' => true],
                [
                    'id' => 'event1',
                    'title' => 'Event 1 title',
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                ],
                function (ApiTestCase $test) {
                    $test->mockEventCall(1);
                },
            ],
        ];
    }
}
