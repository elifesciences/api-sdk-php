<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsPost;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Serializer\LabsPostNormalizer;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class LabsPostNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var LabsPostNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new LabsPostNormalizer(new LabsClient($this->getHttpClient()));
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
    public function it_can_normalize_labs_posts($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $labsPost = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            $thumbnail, rejection_for('No social image'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        return [
            'Labs post' => [$labsPost, null, true],
            'Labs post with format' => [$labsPost, 'foo', true],
            'non-Labs post' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_labs_posts(LabsPost $labsPost, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($labsPost, null, $context));
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
    public function it_can_denormalize_labs_posts($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'Labs post' => [[], LabsPost::class, [], true],
            'Labs post by type' => [['type' => 'labs-post'], Model::class, [], true],
            'non-Labs post' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_labs_posts(
        LabsPost $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, LabsPost::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $published = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updated = new DateTimeImmutable('now', new DateTimeZone('Z'));
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $socialImage = Builder::for(Image::class)->sample('social');

        return [
            'complete' => [
                new LabsPost('80000001', 'title', $published, $updated, 'impact statement', $thumbnail, promise_for($socialImage),
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => '80000001',
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'image' => [
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
                    'impactStatement' => 'impact statement',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new LabsPost('80000001', 'title', $published, null, null, $thumbnail, promise_for(null),
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => '80000001',
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'image' => [
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
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new LabsPost('80000001', 'Labs post 1 title', $published, $updated, 'Labs post 1 impact statement',
                    $thumbnail, promise_for(null), new ArraySequence([new Paragraph('Labs post 80000001 text')])),
                ['snippet' => true, 'type' => true],
                [
                    'id' => '80000001',
                    'title' => 'Labs post 1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'image' => [
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
                    'impactStatement' => 'Labs post 1 impact statement',
                    'type' => 'labs-post',
                ],
                function (ApiTestCase $test) {
                    $test->mockLabsPostCall('80000001', true);
                },
            ],
            'minimum snippet' => [
                new LabsPost('80000001', 'Labs post 1 title', $published, null, null, $thumbnail, promise_for(null),
                    new ArraySequence([new Paragraph('Labs post 80000001 text')])),
                ['snippet' => true],
                [
                    'id' => '80000001',
                    'title' => 'Labs post 1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'image' => [
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
                function (ApiTestCase $test) {
                    $test->mockLabsPostCall('80000001');
                },
            ],
        ];
    }

    protected function class() : string
    {
        return LabsPost::class;
    }

    protected function samples()
    {
        yield __DIR__."/../../vendor/elife/api/dist/samples/community-list/v1/*.json#items[?type=='labs-post']";
        yield __DIR__.'/../../vendor/elife/api/dist/samples/labs-post/v2/*.json';
        yield __DIR__.'/../../vendor/elife/api/dist/samples/labs-post-list/v1/*.json#items';
        yield __DIR__."/../../vendor/elife/api/dist/samples/search/v1/*.json#items[?type=='labs-post']";
    }
}
