<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Serializer\PodcastEpisodeChapterModelNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
use function GuzzleHttp\Promise\promise_for;

final class PodcastEpisodeChapterModelNormalizerTest extends ApiTestCase
{
    /** @var PodcastEpisodeChapterModelNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new PodcastEpisodeChapterModelNormalizer();
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
    public function it_can_normalize_podcast_episode_chapter_models($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $model = new PodcastEpisodeChapterModel(Builder::dummy(PodcastEpisode::class), new PodcastEpisodeChapter(1, 'title', 0, null, new EmptySequence()));

        return [
            'podcast episode chapter model' => [$model, null, true],
            'podcast episode chapter model with format' => [$model, 'foo', true],
            'non-podcast episode chapter model' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_podcast_episode_chapter_models(PodcastEpisodeChapterModel $model, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($model, null, $context));
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
    public function it_can_denormalize_podcast_episode_chapter_models($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'podcast episode chapter model' => [[], PodcastEpisodeChapterModel::class, [], true],
            'podcast episode chapter model by type' => [['type' => 'podcast-episode-chapter'], Model::class, [], true],
            'non-podcast episode chapter model' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_podcast_episode_chapter_models(
        PodcastEpisodeChapterModel $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, PodcastEpisodeChapterModel::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable('2000-01-01', new DateTimeZone('Z'));
        $banner = new Image('',
            [new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900'])]);
        $thumbnail = new Image('', [
            new ImageSize('16:9', [
                250 => 'https://placehold.it/250x141',
                500 => 'https://placehold.it/500x281',
            ]),
            new ImageSize('1:1', [
                '70' => 'https://placehold.it/70x70',
                '140' => 'https://placehold.it/140x140',
            ]),
        ]);

        return [
            [
                new PodcastEpisodeChapterModel(
                    new PodcastEpisode(1, 'Podcast episode 1 title', 'Podcast episode 1 impact statement', $date, null,
                        promise_for($banner), $thumbnail,
                        [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
                        new EmptySequence(), new ArraySequence([
                            $chapter = new PodcastEpisodeChapter(1, 'Chapter title', 0, null, new EmptySequence()),
                        ])
                    ),
                    $chapter
                ),
                [],
                [
                    'type' => 'podcast-episode-chapter',
                    'episode' => [
                        'number' => 1,
                        'title' => 'Podcast episode 1 title',
                        'published' => $date->format(ApiSdk::DATE_FORMAT),
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
                        'sources' => [
                            [
                                'mediaType' => 'audio/mpeg',
                                'uri' => 'https://www.example.com/episode.mp3',
                            ],
                        ],
                        'impactStatement' => 'Podcast episode 1 impact statement',
                    ],
                    'chapter' => [
                        'number' => 1,
                        'title' => 'Chapter title',
                        'time' => 0,
                    ],
                ],
                function ($test) {
                    $test->mockPodcastEpisodeCall(1);
                },
            ],
        ];
    }
}
