<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Serializer\ArticleVoRNormalizer;
use eLife\ApiSdk\Serializer\CoverNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class CoverNormalizerTest extends ApiTestCase
{
    /** @var CoverNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new CoverNormalizer();
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
    public function it_can_normalize_covers($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $cover = new Cover('title', $image, Builder::dummy(ArticleVoR::class));

        return [
            'cover' => [$cover, null, true],
            'cover with format' => [$cover, 'foo', true],
            'non-cover' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_covers(Cover $cover, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($cover));
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
    public function it_can_denormalize_covers($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'cover' => [[], Cover::class, [], true],
            'non-cover' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_covers(Cover $expected, array $json, callable $extra = null)
    {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Cover::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900'])]);

        return [
            [
                new Cover('title', $image, Builder::for (ArticleVoR::class)->sample('homo-naledi')),
                [
                    'title' => 'title',
                    'image' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                900 => 'https://placehold.it/900x450',
                                1800 => 'https://placehold.it/1800x900',
                            ],
                        ],
                    ],
                    'item' => [
                        'id' => '09560',
                        'stage' => 'published',
                        'version' => 1,
                        'type' => 'research-article',
                        'doi' => '10.7554/eLife.09560',
                        'authorLine' => 'Lee R Berger et al',
                        'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                        'volume' => 4,
                        'elocationId' => 'e09560',
                        'published' => '2015-09-10T00:00:00Z',
                        'versionDate' => '2015-09-10T00:00:00Z',
                        'statusDate' => '2015-09-10T00:00:00Z',
                        'pdf' => 'https://elifesciences.org/content/4/e09560.pdf',
                        'subjects' => [
                            0 => [
                                'id' => 'genomics-evolutionary-biology',
                                'name' => 'Genomics and Evolutionary Biology',
                            ],
                        ],
                        'status' => 'vor',
                        'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                        'image' => [
                            'thumbnail' => [
                                'alt' => '',
                                'sizes' => [
                                    '16:9' => [
                                        250 => 'https://placehold.it/250x141',
                                        500 => 'https://placehold.it/500x281',
                                    ],
                                    '1:1' => [
                                        70 => 'https://placehold.it/70x70',
                                        140 => 'https://placehold.it/140x140',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockSubjectCall('genomics-evolutionary-biology', true);
                    $test->mockArticleCall('09560', true, true);
                },
            ],
        ];
    }
}
