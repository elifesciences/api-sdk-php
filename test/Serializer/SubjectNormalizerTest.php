<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiClient\SubjectsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
use PHPUnit\Framework\Attributes\Before as Before;

final class SubjectNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var SubjectNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new SubjectNormalizer(new SubjectsClient($this->getHttpClient()));
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
    public function it_can_normalize_subjects($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $subject = new Subject('id', 'name', Create::promiseFor(null), new EmptySequence(), Create::promiseFor($banner), Create::promiseFor($thumbnail));

        return [
            'subject' => [$subject, null, true],
            'subject with format' => [$subject, 'foo', true],
            'non-subject' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_subjects(Subject $subject, array $context, array $expected, callable $extra = null)
    {
        if (!empty($context['snippet'])) {
            $this->mockSubjectCall('subject1');
        }

        $this->assertSame($expected, $this->normalizer->normalize($subject, null, $context));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_subjects($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'subject' => [[], Subject::class, [], true],
            'non-subject' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_subjects(
        Subject $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Subject::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
    {
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');

        return [
            'complete' => [
                new Subject('subject1', 'Subject 1 name', Create::promiseFor('Subject subject1 impact statement'),
                    new ArraySequence([new Paragraph('Subject subject1 aims and scope')]), Create::promiseFor($banner), Create::promiseFor($thumbnail)),
                [],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                    'image' => [
                        'banner' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                                'filename' => 'banner.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 900,
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                    'impactStatement' => 'Subject subject1 impact statement',
                    'aimsAndScope' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Subject subject1 aims and scope',
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Subject('subject1', 'Subject 1 name', Create::promiseFor(null), new EmptySequence(),
                    Create::promiseFor($banner), Create::promiseFor($thumbnail)),
                [],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                    'image' => [
                        'banner' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                                'filename' => 'banner.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 900,
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new Subject('subject1', 'Subject 1 name', Create::promiseFor('Subject subject1 impact statement'),
                    new ArraySequence([new Paragraph('Subject subject1 aims and scope')]), Create::promiseFor($banner), Create::promiseFor($thumbnail)),
                ['snippet' => true],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                ],
                function (ApiTestCase $test) {
                    $test->mockSubjectCall('subject1', true);
                },
            ],
            'minimum snippet' => [
                new Subject('subject1', 'Subject 1 name', Create::promiseFor('Subject subject1 impact statement'),
                    new EmptySequence(), Create::promiseFor($banner), Create::promiseFor($thumbnail)),
                ['snippet' => true],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                ],
                function (ApiTestCase $test) {
                    $test->mockSubjectCall('subject1');
                },
            ],
        ];
    }

    protected function class() : string
    {
        return Subject::class;
    }

    protected static function samples(): \Generator
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/subject/v1/*.json';
        yield [__DIR__.'/../../vendor/elife/api/dist/samples/subject-list/v1/*.json#items', ['snippet' => false]];
    }
}
