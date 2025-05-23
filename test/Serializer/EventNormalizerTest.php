<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiClient\EventsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Serializer\EventNormalizer;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class EventNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

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
        $event = new Event('id', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No social image'),
            new PromiseSequence(rejection_for('Event content should not be unwrapped')));

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
        $published = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updated = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $starts = new DateTimeImmutable('2017-01-01T14:00:00Z', new DateTimeZone('Z'));
        $ends = new DateTimeImmutable('2017-01-01T16:00:00Z', new DateTimeZone('Z'));
        $timezone = new DateTimeZone('Europe/London');

        return [
            'complete with content' => [
                new Event('id', 'title', 'impact statement', $published, $updated, $starts, $ends, $timezone, null,
                    promise_for(Builder::for(Image::class)->sample('social')),
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                    'updated' => $published->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'impact statement',
                    'timezone' => $timezone->getName(),
                    'image' => [
                        'social' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/social.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                                'filename' => 'social.jpg',
                            ],
                            'size' => [
                                'width' => 600,
                                'height' => 600,
                            ],
                        ],
                    ],
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete with uri' => [
                new Event('id', 'title', 'impact statement', $published, $updated, $starts, $ends, $timezone, 'http://www.example.com/',
                    promise_for(Builder::for(Image::class)->sample('social')), new EmptySequence()),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                    'updated' => $published->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'impact statement',
                    'timezone' => $timezone->getName(),
                    'uri' => 'http://www.example.com/',
                    'image' => [
                        'social' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/social.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                                'filename' => 'social.jpg',
                            ],
                            'size' => [
                                'width' => 600,
                                'height' => 600,
                            ],
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Event('id', 'title', null, $published, null, $starts, $ends, null, null, promise_for(null), new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
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
            'complete snippet with content' => [
                new Event('event1', 'Event event1 title', 'Event event1 impact statement', $published, $updated, $starts, $ends, $timezone, null, promise_for(Builder::for(Image::class)->sample('social')),
                    new ArraySequence([new Paragraph('Event event1 text')])),
                ['snippet' => true, 'type' => true],
                [
                    'id' => 'event1',
                    'title' => 'Event event1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'Event event1 impact statement',
                    'timezone' => $timezone->getName(),
                    'type' => 'event',
                ],
                function (ApiTestCase $test) {
                    $test->mockEventCall('event1', true);
                },
            ],
            'complete snippet with uri' => [
                new Event('event1', 'Event event1 title', 'Event event1 impact statement', $published, $updated, $starts, $ends, $timezone, 'http://www.example.com/', promise_for(Builder::for(Image::class)->sample('social')), new EmptySequence()),
                ['snippet' => true, 'type' => true],
                [
                    'id' => 'event1',
                    'title' => 'Event event1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'Event event1 impact statement',
                    'timezone' => $timezone->getName(),
                    'type' => 'event',
                    'uri' => 'http://www.example.com/',
                ],
                function (ApiTestCase $test) {
                    $test->mockEventCall('event1', true, true);
                },
            ],
            'minimum snippet' => [
                new Event('event1', 'Event event1 title', null, $published, null, $starts, $ends, null, null, promise_for(null),
                    new ArraySequence([new Paragraph('Event event1 text')])),
                ['snippet' => true],
                [
                    'id' => 'event1',
                    'title' => 'Event event1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                    'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                ],
                function (ApiTestCase $test) {
                    $test->mockEventCall('event1');
                },
            ],
        ];
    }

    protected function class() : string
    {
        return Event::class;
    }

    protected function samples()
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/event/v2/*.json';
        yield __DIR__.'/../../vendor/elife/api/dist/samples/event-list/v1/*.json#items';
        yield __DIR__."/../../vendor/elife/api/dist/samples/community-list/v1/*.json#items[?type=='event']";
    }
}
