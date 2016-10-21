<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\PodcastEpisodeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class PodcastEpisodeNormalizerTest extends ApiTestCase
{
    /** @var PodcastEpisodeNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new PodcastEpisodeNormalizer(new PodcastClient($this->getHttpClient()));
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
    public function it_can_normalize_podcast_episodes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable(), rejection_for('No banner'),
            new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        return [
            'podcast episode' => [$podcastEpisode, null, true],
            'podcast episode with format' => [$podcastEpisode, 'foo', true],
            'non-podcast episode' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_podcast_episodes(PodcastEpisode $podcastEpisode, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($podcastEpisode, null, $context));
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
    public function it_can_denormalize_podcast_episodes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'podcast episode' => [[], PodcastEpisode::class, [], true],
            'non-podcast episode' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_podcast_episodes(
        PodcastEpisode $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, PodcastEpisode::class, null, $context);

        $this->mockSubjectCall(1);
        $this->mockArticleCall(1, !empty($context['complete']));

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable();
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
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject 1 impact statement'),
            promise_for($banner), promise_for($thumbnail));

        return [
            'complete' => [
                new PodcastEpisode(1, 'Podcast episode 1 title', 'Podcast episode 1 impact statement', $date,
                    promise_for($banner), $thumbnail,
                    [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
                    new ArraySequence([]), new ArraySequence([
                        new PodcastEpisodeChapter(1, 'Chapter 1 title', 0, 'Chapter impact statement',
                            new ArraySequence([
                                new ArticlePoA('article1', 1, 'research-article', '10.7554/eLife.1', 'Author et al',
                                    'Article 1 title prefix', 'Article 1 title',
                                    new DateTimeImmutable('2000-01-01T00:00:00+00:00'),
                                    new DateTimeImmutable('1999-12-31T00:00:00+00:00'), 1, 'e1',
                                    'http://www.example.com/',
                                    new ArraySequence([$subject]), ['Article 1 research organism'],
                                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 1 abstract text')]))),
                                    promise_for(1),
                                    promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                                    new ArraySequence([new PersonAuthor(new Person('Author', 'Author'))])),
                            ])),
                    ])),
                ['complete' => true],
                [
                    'number' => 1,
                    'title' => 'Podcast episode 1 title',
                    'published' => $date->format(DATE_ATOM),
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
                        'banner' => [
                            'alt' => '',
                            'sizes' => [
                                '2:1' => [
                                    900 => 'https://placehold.it/900x450',
                                    1800 => 'https://placehold.it/1800x900',
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
                    'chapters' => [
                        [
                            'number' => 1,
                            'title' => 'Chapter 1 title',
                            'time' => 0,
                            'content' => [
                                [
                                    'id' => 'article1',
                                    'version' => 1,
                                    'type' => 'research-article',
                                    'doi' => '10.7554/eLife.1',
                                    'authorLine' => 'Author et al',
                                    'title' => 'Article 1 title',
                                    'published' => '2000-01-01T00:00:00+00:00',
                                    'statusDate' => '1999-12-31T00:00:00+00:00',
                                    'volume' => 1,
                                    'elocationId' => 'e1',
                                    'titlePrefix' => 'Article 1 title prefix',
                                    'pdf' => 'http://www.example.com/',
                                    'subjects' => [
                                        ['id' => 'subject1', 'name' => 'Subject 1 name'],
                                    ],
                                    'researchOrganisms' => ['Article 1 research organism'],
                                    'status' => 'poa',
                                ],
                            ],
                            'impactStatement' => 'Chapter impact statement',
                        ],
                    ],
                    'impactStatement' => 'Podcast episode 1 impact statement',
                ],
            ],
            'minimum' => [
                new PodcastEpisode(1, 'Podcast episode 1 title', null, $date, promise_for($banner), $thumbnail,
                    [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
                    new ArraySequence([]), new ArraySequence([
                        new PodcastEpisodeChapter(1, 'Chapter title', 0, null, new ArraySequence([
                            new ArticlePoA('article1', 1, 'research-article', '10.7554/eLife.1', 'Author et al', null,
                                'Article 1 title', new DateTimeImmutable('2000-01-01T00:00:00+00:00'),
                                new DateTimeImmutable('1999-12-31T00:00:00+00:00'), 1, 'e1', null,
                                new ArraySequence([]), [], promise_for(null), promise_for(null),
                                promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                                new ArraySequence([new PersonAuthor(new Person('Author', 'Author'))])),
                        ])),
                    ])),
                [],
                [
                    'number' => 1,
                    'title' => 'Podcast episode 1 title',
                    'published' => $date->format(DATE_ATOM),
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
                        'banner' => [
                            'alt' => '',
                            'sizes' => [
                                '2:1' => [
                                    900 => 'https://placehold.it/900x450',
                                    1800 => 'https://placehold.it/1800x900',
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
                    'chapters' => [
                        [
                            'number' => 1,
                            'title' => 'Chapter title',
                            'time' => 0,
                            'content' => [
                                [
                                    'id' => 'article1',
                                    'version' => 1,
                                    'type' => 'research-article',
                                    'doi' => '10.7554/eLife.1',
                                    'authorLine' => 'Author et al',
                                    'title' => 'Article 1 title',
                                    'published' => '2000-01-01T00:00:00+00:00',
                                    'statusDate' => '1999-12-31T00:00:00+00:00',
                                    'volume' => 1,
                                    'elocationId' => 'e1',
                                    'status' => 'poa',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new PodcastEpisode(1, 'Podcast episode 1 title', 'Podcast episode 1 impact statement', $date,
                    promise_for($banner), $thumbnail,
                    [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
                    new ArraySequence([]), new ArraySequence([
                        new PodcastEpisodeChapter(1, 'Chapter title', 0, 'Chapter impact statement', new ArraySequence([
                            new ArticlePoA('article1', 1, 'research-article', '10.7554/eLife.1', 'Author et al',
                                'Article 1 title prefix', 'Article 1 title',
                                new DateTimeImmutable('2000-01-01T00:00:00+00:00'),
                                new DateTimeImmutable('1999-12-31T00:00:00+00:00'), 1, 'e1', 'http://www.example.com/',
                                new ArraySequence([$subject]), ['Article 1 research organism'],
                                promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 1 abstract text')]))),
                                promise_for(1), promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                                new ArraySequence([new PersonAuthor(new Person('Author', 'Author'))])),
                        ])),
                    ])),
                ['snippet' => true, 'complete' => true],
                [
                    'number' => 1,
                    'title' => 'Podcast episode 1 title',
                    'impactStatement' => 'Podcast episode 1 impact statement',
                    'published' => $date->format(DATE_ATOM),
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
                ],
                function (ApiTestCase $test) {
                    $test->mockPodcastEpisodeCall(1, true);
                },
            ],
            'minimum snippet' => [
                new PodcastEpisode(1, 'Podcast episode 1 title', null, $date, promise_for($banner), $thumbnail,
                    [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
                    new ArraySequence([]), new ArraySequence([
                        new PodcastEpisodeChapter(1, 'Chapter title', 0, null, new ArraySequence([
                            new ArticlePoA('article1', 1, 'research-article', '10.7554/eLife.1', 'Author et al', null,
                                'Article 1 title', new DateTimeImmutable('2000-01-01T00:00:00+00:00'),
                                new DateTimeImmutable('1999-12-31T00:00:00+00:00'), 1, 'e1', null,
                                new ArraySequence([]), [], promise_for(null), promise_for(null),
                                promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                                new ArraySequence([new PersonAuthor(new Person('Author', 'Author'))])),
                        ])),
                    ])),
                ['snippet' => true],
                [
                    'number' => 1,
                    'title' => 'Podcast episode 1 title',
                    'published' => $date->format(DATE_ATOM),
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
                ],
                function (ApiTestCase $test) {
                    $test->mockPodcastEpisodeCall(1);
                },
            ],
        ];
    }
}
