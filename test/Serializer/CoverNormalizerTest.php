<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Serializer\CoverNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class CoverNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

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
        $image = Builder::for(Image::class)->sample('banner');
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
        $image = Builder::for(Image::class)->sample('banner');

        return [
            [
                new Cover('title', $image, Builder::for(ArticlePoA::class)->sample('growth-factor')),
                [
                    'title' => 'title',
                    'image' => [
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
                    'item' => [
                        'id' => '14107',
                        'stage' => 'published',
                        'version' => 1,
                        'type' => 'research-article',
                        'doi' => '10.7554/eLife.14107',
                        'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                        'volume' => 5,
                        'elocationId' => 'e14107',
                        'published' => '2016-03-28T00:00:00Z',
                        'versionDate' => '2016-03-28T00:00:00Z',
                        'statusDate' => '2016-03-28T00:00:00Z',
                        'authorLine' => 'Yongjian Huang et al',
                        'abstract' => [
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Article 14107 abstract text',
                                ],
                            ],
                        ],
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
                        'status' => 'poa',
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall('14107', true, false, 1);
                },
            ],
        ];
    }

    protected function class() : string
    {
        return Cover::class;
    }

    protected function samples()
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/cover-list/v1/*.json#items';
    }
}
