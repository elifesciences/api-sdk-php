<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiClient\ReviewedPreprintsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ReviewedPreprintNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class ReviewedPreprintNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var ReviewedPreprintNormalizer */
    private $normalizer;

    private $builder;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new ReviewedPreprintNormalizer(new ReviewedPreprintsClient($this->getHttpClient()));
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
    public function it_can_normalize_reviewed_preprints($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reviewedPreprint = Builder::for(ReviewedPreprint::class)->sample('minimum');

        return [
            'reviewed preprint' => [$reviewedPreprint, null, true],
            'reviewed preprint with format' => [$reviewedPreprint, 'foo', true],
            'non reviewed preprint' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_reviewed_preprints(ReviewedPreprint $reviewedPreprint, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($reviewedPreprint, null, $context));
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
    public function it_can_denormalize_reviewed_preprints($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'preprint' => [[], ReviewedPreprint::class, [], true],
            'non-preprint' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_reviewed_preprints(
        ReviewedPreprint $expected,
        array $context,
        array $json,
        callable $extra = null
    )
    {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, ReviewedPreprint::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                Builder::for(ReviewedPreprint::class)
                    ->withVolume(4)
                    ->withVersion(2)
                    ->withElocationId('e19560')
                    ->withPdf('http://www.example.com/pdf')
                    ->withCurationLabels(['one', 'two'])
                    ->withSubjects(new ArraySequence([
                        Builder::for(Subject::class)
                            ->withId('subject1')
                            ->__invoke(),
                    ]))
                    ->withElifeAssessment(new ElifeAssessment(null, ['landmark'], ['solid']))
                    ->__invoke(),
                [],
                [
                    'id'=> '1',
                    'status' => 'reviewed',
                    'title' => 'Reviewed preprint',
                    'stage' => 'published',
                    'doi' => '10.7554/eLife.19560',
                    'authorLine' => 'Lee R Berger, John Hawks ... Scott A Williams',
                    'titlePrefix' => 'Title prefix',
                    'published' => '2022-08-01T00:00:00Z',
                    'reviewedDate' => '2022-08-01T00:00:00Z',
                    'versionDate' => '2022-08-01T00:00:00Z',
                    'statusDate' => '2022-08-01T00:00:00Z',
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
                    'indexContent' => 'Reviewed preprint',
                    'volume' => 4,
                    'version' => 2,
                    'elocationId' => 'e19560',
                    'pdf' => 'http://www.example.com/pdf',
                    'curationLabels' => [
                        'one',
                        'two',
                    ],
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1'],
                    ],
                    'elifeAssessment' => [
                        'significance' => ['landmark'],
                        'strength' => ['solid'],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockSubjectCall('subject1');
                },
            ],
            'minimum' => [
                Builder::for(ReviewedPreprint::class)
                    ->withDoi(null)
                    ->withAuthorLine(null)
                    ->withTitlePrefix(null)
                    ->withPublished(null)
                    ->withReviewedDate(null)
                    ->withVersionDate(null)
                    ->withStatusDate(null)
                    ->withThumbnail(null)
                    ->withPromiseOfIndexContent(null)
                    ->withVersion(null)
                    ->__invoke(),
                [],
                [
                    'id'=> '1',
                    'status' => 'reviewed',
                    'title' => 'Reviewed preprint',
                    'stage' => 'published',
                ],
                function (ApiTestCase $test) {
                    $test->mockReviewedPreprintCall('1', false, true);
                },
            ],
            'complete snippet' => [
                Builder::for(ReviewedPreprint::class)
                    ->withVolume(4)
                    ->withVersion(2)
                    ->withElocationId('e19560')
                    ->withPdf('http://www.example.com/pdf')
                    ->withCurationLabels(['one', 'two'])
                    ->withSubjects(new ArraySequence([
                        Builder::for(Subject::class)
                            ->withId('subject1')
                            ->__invoke(),
                    ]))
                    ->withElifeAssessment(new ElifeAssessment(null, ['landmark'], ['solid']))
                    ->withPromiseOfIndexContent(null)
                    ->__invoke(),
                ['snippet' => true, 'type' => true],
                [
                    'id'=> '1',
                    'type' => 'reviewed-preprint',
                    'status' => 'reviewed',
                    'title' => 'Reviewed preprint',
                    'stage' => 'published',
                    'doi' => '10.7554/eLife.19560',
                    'authorLine' => 'Lee R Berger, John Hawks ... Scott A Williams',
                    'titlePrefix' => 'Title prefix',
                    'published' => '2022-08-01T00:00:00Z',
                    'reviewedDate' => '2022-08-01T00:00:00Z',
                    'versionDate' => '2022-08-01T00:00:00Z',
                    'statusDate' => '2022-08-01T00:00:00Z',
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
                    'volume' => 4,
                    'version' => 2,
                    'elocationId' => 'e19560',
                    'pdf' => 'http://www.example.com/pdf',
                    'curationLabels' => [
                        'one',
                        'two',
                    ],
                    'elifeAssessment' => [
                        'significance' => ['landmark'],
                        'strength' => ['solid'],
                    ],
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1'],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockReviewedPreprintCall('1', true, true);
                    $test->mockSubjectCall('subject1');
                },
            ],
            'minimum snippet' => [
                Builder::for(ReviewedPreprint::class)
                    ->withDoi(null)
                    ->withAuthorLine(null)
                    ->withTitlePrefix(null)
                    ->withPublished(null)
                    ->withReviewedDate(null)
                    ->withVersionDate(null)
                    ->withStatusDate(null)
                    ->withThumbnail(null)
                    ->withPromiseOfIndexContent(null)
                    ->withVersion(null)
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id'=> '1',
                    'status' => 'reviewed',
                    'title' => 'Reviewed preprint',
                    'stage' => 'published',
                ],
                function (ApiTestCase $test) {
                    $test->mockReviewedPreprintCall('1', false, true);
                },
            ],
        ];
    }

    protected function class() : string
    {
        return ReviewedPreprint::class;
    }

    protected function samples()
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/reviewed-preprint/v1/*.json';
        yield __DIR__.'/../../vendor/elife/api/dist/samples/reviewed-preprint-list/v1/*.json#items';
        yield __DIR__."/../../vendor/elife/api/dist/samples/search/v2/*.json#items[?type=='reviewed-prerint']";
    }
}
