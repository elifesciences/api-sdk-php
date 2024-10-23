<?php

namespace test\eLife\ApiSdk\Serializer;

use function call_user_func;
use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiClient\DigestsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\DigestNormalizer;
use function get_class;
use function GuzzleHttp\Promise\promise_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class DigestNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var DigestNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new DigestNormalizer(new DigestsClient($this->getHttpClient()));
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
    public function it_can_normalize_digests($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $digest = Builder::dummy(Digest::class);

        return [
            'digest' => [$digest, null, true],
            'digest with format' => [$digest, 'foo', true],
            'non-digest' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_digests(Digest $digest, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($digest, null, $context));
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
    public function it_can_denormalize_digests($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'digest' => [[], Digest::class, [], true],
            'digest by type' => [['type' => 'digest'], Model::class, [], true],
            'non-digest' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_digests(
        Digest $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Digest::class, null, $context);

        $this->mockSubjectCall(1);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $published = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updated = new DateTimeImmutable('now', new DateTimeZone('Z'));
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $socialImage = Builder::for(Image::class)->sample('social');
        $content = new Paragraph('Digest 1 text');
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
            new EmptySequence(), promise_for($banner), promise_for($thumbnail));
        $article = Builder::for(ArticleVoR::class)->sample('homo-naledi');

        return [
            'complete' => [
                Builder::for(Digest::class)
                    ->withId('1')
                    ->withTitle('Digest 1 title')
                    ->withImpactStatement('Digest 1 impact statement')
                    ->withStage('published')
                    ->withPublished($published)
                    ->withUpdated($updated)
                    ->withThumbnail($thumbnail)
                    ->withSocialImage($socialImage)
                    ->withSequenceOfSubjects($subject)
                    ->withSequenceOfContent($content)
                    ->withSequenceOfRelatedContent($article)
                    ->__invoke(),
                [],
                [
                    'id' => '1',
                    'title' => 'Digest 1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'Digest 1 impact statement',
                    'stage' => 'published',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Digest 1 text',
                        ],
                    ],
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1 name'],
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
                    'relatedContent' => [
                        [
                            'type' => 'research-article',
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '09560',
                            'version' => 1,
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
                                [
                                    'id' => 'genomics-evolutionary-biology',
                                    'name' => 'Genomics and Evolutionary Biology',
                                ],
                            ],
                            'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                            'abstract' => [
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'text' => 'Article 09560 abstract text',
                                    ],
                                ],
                                'doi' => '10.7554/eLife.09560abstract',
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
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockSubjectCall('genomics-evolutionary-biology', true);
                    $test->mockArticleCall('09560', true, true, 1);
                },
            ],
            'minimum' => [
                Builder::for(Digest::class)
                    ->withId('1')
                    ->withTitle('Digest 1 title')
                    ->withStage('preview')
                    ->withThumbnail($thumbnail)
                    ->withSequenceOfContent($content)
                    ->withSequenceOfRelatedContent($article)
                    ->__invoke(),
                [],
                [
                    'id' => '1',
                    'title' => 'Digest 1 title',
                    'stage' => 'preview',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Digest 1 text',
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
                    ],
                    'relatedContent' => [
                        [
                            'type' => 'research-article',
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '09560',
                            'version' => 1,
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
                                [
                                    'id' => 'genomics-evolutionary-biology',
                                    'name' => 'Genomics and Evolutionary Biology',
                                ],
                            ],
                            'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                            'abstract' => [
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'text' => 'Article 09560 abstract text',
                                    ],
                                ],
                                'doi' => '10.7554/eLife.09560abstract',
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
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockSubjectCall('genomics-evolutionary-biology', true);
                    $test->mockArticleCall('09560', true, true, 1);
                },
            ],
            'complete snippet' => [
                Builder::for(Digest::class)
                    ->withId('1')
                    ->withTitle('Digest 1 title')
                    ->withImpactStatement('Digest 1 impact statement')
                    ->withStage('published')
                    ->withPublished($published)
                    ->withUpdated($updated)
                    ->withThumbnail($thumbnail)
                    ->withSocialImage($socialImage)
                    ->withSequenceOfSubjects($subject)
                    ->withSequenceOfContent($content)
                    ->withSequenceOfRelatedContent($article)
                    ->__invoke(),
                ['snippet' => true, 'type' => true],
                [
                    'type' => 'digest',
                    'id' => '1',
                    'title' => 'Digest 1 title',
                    'stage' => 'published',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'Digest 1 impact statement',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1 name'],
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
                ],
                function (ApiTestCase $test) {
                    $test->mockSubjectCall('genomics-evolutionary-biology', true);
                    $test->mockArticleCall('09560', true, true, 1);
                    $test->mockDigestCall(1, true);
                },
            ],
            'minimum snippet' => [
                Builder::for(Digest::class)
                    ->withId('1')
                    ->withTitle('Digest 1 title')
                    ->withStage('preview')
                    ->withThumbnail($thumbnail)
                    ->withSequenceOfContent($content)
                    ->withSequenceOfRelatedContent($article)
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => '1',
                    'title' => 'Digest 1 title',
                    'stage' => 'preview',
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
                    $test->mockSubjectCall('genomics-evolutionary-biology', true);
                    $test->mockArticleCall('09560', true, true, 1);
                    $test->mockDigestCall(1, true);
                },
            ],
        ];
    }

    protected function class() : string
    {
        return Digest::class;
    }

    protected function samples()
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/digest/v1/*.json';
        yield __DIR__.'/../../vendor/elife/api/dist/samples/digest-list/v1/*.json#items';
    }
}
