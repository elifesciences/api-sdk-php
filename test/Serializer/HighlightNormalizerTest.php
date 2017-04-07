<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Serializer\HighlightNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class HighlightNormalizerTest extends ApiTestCase
{
    /** @var HighlightNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new HighlightNormalizer();
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
    public function it_can_normalize_highlights($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $highlight = new Highlight('title', null, null, Builder::dummy(ArticleVoR::class));

        return [
            'Highlight' => [$highlight, null, true],
            'Highlight with format' => [$highlight, 'foo', true],
            'non-Highlight' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_highlights(Highlight $highlight, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($highlight, null, $context));
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
    public function it_can_denormalize_highlights($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'Highlight' => [[], Highlight::class, [], true],
            'non-Highlight' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_highlights(
        Highlight $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Highlight::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable('now', new DateTimeZone('Z'));
        $image = Builder::for(Image::class)->sample('thumbnail');

        return [
            'complete' => [
                new Highlight('title', 'Author et al', $image, Builder::dummy(Interview::class)),
                [],
                [
                    'title' => 'title',
                    'authorLine' => 'Author et al',
                    'image' => [
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
                    'item' => [
                        'id' => '1',
                        'interviewee' => [
                            'name' => [
                                'preferred' => 'Ramanath Hegde',
                                'index' => 'Hegde, Ramanath',
                            ],
                        ],
                        'title' => 'Controlling traffic',
                        'published' => $date->format(ApiSdk::DATE_FORMAT),
                        'type' => 'interview',
                    ],
                ],
                function ($test) {
                    $test->mockInterviewCall('1');
                },
            ],
            'minimum' => [
                new Highlight('title', null, null, Builder::dummy(Interview::class)),
                [],
                [
                    'title' => 'title',
                    'item' => [
                        'id' => '1',
                        'interviewee' => [
                            'name' => [
                                'preferred' => 'Ramanath Hegde',
                                'index' => 'Hegde, Ramanath',
                            ],
                        ],
                        'title' => 'Controlling traffic',
                        'published' => $date->format(ApiSdk::DATE_FORMAT),
                        'type' => 'interview',
                    ],
                ],
                function ($test) {
                    $test->mockInterviewCall('1');
                },
            ],
        ];
    }
}
