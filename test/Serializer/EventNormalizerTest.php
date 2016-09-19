<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\EventNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class EventNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var EventNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new EventNormalizer();

        $serializer = new Serializer([
            $this->normalizer,
            new PlaceNormalizer(),
            new Block\ParagraphNormalizer(),
        ]);
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
        $event = new Event('id', 'title', null, new DateTimeImmutable(), new DateTimeImmutable(), null,
            new PromiseCollection(rejection_for('Event content should not be unwrapped')),
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

    public function normalizeProvider() : array
    {
        $starts = new DateTimeImmutable();
        $ends = new DateTimeImmutable();
        $timezone = new DateTimeZone('Europe/London');
        $venue = new Place(null, null, ['venue']);

        return [
            'complete' => [
                new Event('id', 'title', 'impact statement', $starts, $ends, $timezone,
                    new ArrayCollection([new Paragraph('text')]), promise_for($venue)),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'starts' => $starts->format(DATE_ATOM),
                    'ends' => $ends->format(DATE_ATOM),
                    'impactStatement' => 'impact statement',
                    'timezone' => $timezone->getName(),
                    'content' => new ArrayCollection([
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ]),
                    'venue' => ['name' => ['venue']],
                ],
            ],
            'minimum' => [
                new Event('id', 'title', null, $starts, $ends, null, new ArrayCollection([new Paragraph('text')])),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'starts' => $starts->format(DATE_ATOM),
                    'ends' => $ends->format(DATE_ATOM),
                    'content' => new ArrayCollection([
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ]),
                ],
            ],
            'complete snippet' => [
                new Event('id', 'title', 'impact statement', $starts, $ends, $timezone,
                    new PromiseCollection(rejection_for('Event content should not be unwrapped')),
                    rejection_for('Event venue should not be unwrapped')),
                ['snippet' => true],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'starts' => $starts->format(DATE_ATOM),
                    'ends' => $ends->format(DATE_ATOM),
                    'impactStatement' => 'impact statement',
                    'timezone' => $timezone->getName(),
                ],
            ],
            'minimum snippet' => [
                new Event('id', 'title', null, $starts, $ends, null,
                    new PromiseCollection(rejection_for('Event content should not be unwrapped')),
                    rejection_for('Event venue should not be unwrapped')),
                ['snippet' => true],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'starts' => $starts->format(DATE_ATOM),
                    'ends' => $ends->format(DATE_ATOM),
                ],
            ],
        ];
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
            'non-event' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_events(array $json, Event $expected)
    {
        $actual = $this->normalizer->denormalize($json, Event::class);

        $normaliseResult = function ($value) use (&$normaliseResult) {
            if ($value instanceof Collection) {
                return $value->toArray();
            } elseif ($value instanceof DateTimeInterface) {
                return $value->format(DATE_ATOM);
            } elseif ($value instanceof DateTimeZone) {
                return $value->getName();
            } elseif ($value instanceof PromiseInterface) {
                return $normaliseResult($value->wait());
            }

            return $value;
        };

        $assertObject = function ($actual, $expected) use ($normaliseResult, &$assertObject) {
            foreach (get_class_methods($actual) as $method) {
                if ('__' === substr($method, 0, 2)) {
                    continue;
                }

                $actualMethod = $normaliseResult($actual->{$method}());
                $expectedMethod = $normaliseResult($expected->{$method}());

                if (is_object($actualMethod)) {
                    $this->assertInstanceOf(get_class($actualMethod), $expectedMethod);
                    $assertObject($actualMethod, $expectedMethod);
                } else {
                    $this->assertEquals($actualMethod, $expectedMethod);
                }
            }
        };

        $assertObject($actual, $expected);
    }

    public function denormalizeProvider() : array
    {
        $starts = new DateTimeImmutable();
        $ends = new DateTimeImmutable();
        $timezone = new DateTimeZone('Europe/London');
        $venue = new Place(null, null, ['venue']);

        return [
            'complete' => [
                [
                    'id' => 'id',
                    'title' => 'title',
                    'starts' => $starts->format(DATE_ATOM),
                    'ends' => $ends->format(DATE_ATOM),
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
                new Event('id', 'title', 'impact statement', $starts, $ends, $timezone,
                    new ArrayCollection([new Paragraph('text')]), promise_for($venue)),
            ],
            'minimum' => [
                [
                    'id' => 'id',
                    'title' => 'title',
                    'starts' => $starts->format(DATE_ATOM),
                    'ends' => $ends->format(DATE_ATOM),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
                new Event('id', 'title', null, $starts, $ends, null, new ArrayCollection([new Paragraph('text')])),
            ],
        ];
    }
}
