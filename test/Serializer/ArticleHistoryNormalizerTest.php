<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticleHistory;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Serializer\ArticleHistoryNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class ArticleHistoryNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var ArticleHistoryNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new ArticleHistoryNormalizer();
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
    public function it_can_normalize_article_histories($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $articleHistory = new ArticleHistory(null, null, new ArraySequence([Builder::dummy(ArticlePoA::class)]));

        return [
            'article history' => [$articleHistory, null, true],
            'article history with format' => [$articleHistory, 'foo', true],
            'non-article history' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_article_histories(ArticleHistory $articleHistory, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($articleHistory));
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
    public function it_can_denormalize_article_histories($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'article history' => [[], ArticleHistory::class, [], true],
            'non-article history' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_article_histories(ArticleHistory $expected, array $json, callable $extra = null)
    {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, ArticleHistory::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new ArticleHistory(
                    Date::fromString('2014-01-01'),
                    Date::fromString('2014-02-01'),
                    new ArraySequence([
                        Builder::dummy(ArticlePoA::class),
                    ])
                ),
                [
                    'versions' => [
                        [
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
                    'received' => '2014-01-01',
                    'accepted' => '2014-02-01',
                ],
                function ($test) {
                    $test->mockArticleCall('14107', true, false, 1);
                },
            ],
            'minimum' => [
                new ArticleHistory(
                    null,
                    null,
                    new ArraySequence([
                        Builder::dummy(ArticlePoA::class),
                    ])
                ),
                [
                    'versions' => [
                        [
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
                ],
                function ($test) {
                    $test->mockArticleCall('14107', true, false, 1);
                },
            ],
        ];
    }

    protected function class() : string
    {
        return ArticleHistory::class;
    }

    protected function samples()
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/article-history/v1/*.json';
    }
}
