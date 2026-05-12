<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiClient\JobAdvertsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\JobAdvert;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Serializer\EventNormalizer;
use eLife\ApiSdk\Serializer\JobAdvertNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
use PHPUnit\Framework\Attributes\Before as Before;

final class JobsAdvertNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var EventNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new JobAdvertNormalizer(new JobAdvertsClient($this->getHttpClient()));
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_job_adverts($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
            new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        return [
            'job advert' => [$jobAdvert, null, true],
            'job advert with format' => [$jobAdvert, 'foo', true],
            'non job advert' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_job_adverts(
        JobAdvert $jobAdvert,
        array $context,
        array $expected,
        callable $extra = null
    ): void
    {
        $this->assertEquals($expected, $this->normalizer->normalize($jobAdvert, null, $context));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_job_adverts($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'job advert' => [[], JobAdvert::class, [], true],
            'job advert event by type' => [['type' => 'job-advert'], Model::class, [], true],
            'non job advert' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_job_adverts(
        JobAdvert $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, JobAdvert::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
    {
        $published = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updated = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $closingDate = new DateTimeImmutable('2017-08-17T00:00:00Z', new DateTimeZone('Z'));

        return [
            'complete' => [
                new JobAdvert('id', 'title', 'impact statement', Create::promiseFor(Builder::for(Image::class)->sample('social')), $published, $closingDate, $updated,
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'closingDate' => $closingDate->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'impact statement',
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
            'minimum' => [
                new JobAdvert('id', 'title', null, Create::promiseFor(null), $published, $closingDate, null, new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'closingDate' => $closingDate->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new JobAdvert('job-advert1', 'Job advert job-advert1 title', 'Job advert job-advert1 impact statement', Create::promiseFor(null), $published, $closingDate, $updated,
                    new ArraySequence([new Paragraph('Job advert job-advert1 text')])),
                ['snippet' => true, 'type' => true],
                [
                    'id' => 'job-advert1',
                    'title' => 'Job advert job-advert1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'closingDate' => $closingDate->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'Job advert job-advert1 impact statement',
                    'type' => 'job-advert',
                ],
                function (ApiTestCase $test) {
                    $test->mockJobAdvertCall('job-advert1', true);
                },
            ],
            'minimum snippet' => [
                new JobAdvert('job-advert1', 'Job advert job-advert1 title', null, Create::promiseFor(null), $published, $closingDate, null,
                    new ArraySequence([new Paragraph('Job advert job-advert1 text')])),
                ['snippet' => true],
                [
                    'id' => 'job-advert1',
                    'title' => 'Job advert job-advert1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'closingDate' => $closingDate->format(ApiSdk::DATE_FORMAT),
                ],
                function (ApiTestCase $test) {
                    $test->mockJobAdvertCall('job-advert1');
                },
            ],
        ];
    }

    protected function class() : string
    {
        return JobAdvert::class;
    }

    protected static function samples(): \Generator
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/job-advert/v1/*.json';
        yield __DIR__.'/../../vendor/elife/api/dist/samples/job-advert-list/v1/*.json#items';
    }
}
