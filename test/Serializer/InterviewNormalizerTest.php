<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Serializer\InterviewNormalizer;
use function GuzzleHttp\Promise\rejection_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class InterviewNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var InterviewNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new InterviewNormalizer(new InterviewsClient($this->getHttpClient()));
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
    public function it_can_normalize_interviews($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null,
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        return [
            'interview' => [$interview, null, true],
            'interview with format' => [$interview, 'foo', true],
            'non-interview' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_interviews(Interview $interview, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($interview, null, $context));
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
    public function it_can_denormalize_interviews($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'interview' => [[], Interview::class, [], true],
            'interview by type' => [['type' => 'interview'], Model::class, [], true],
            'non-interview' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_interviews(
        Interview $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Interview::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $published = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updated = new DateTimeImmutable('now', new DateTimeZone('Z'));

        return [
            'complete' => [
                $interview = new Interview('id',
                    new Interviewee(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'),
                        new ArraySequence([new IntervieweeCvLine('date', 'text')])), 'title',
                    $published, $updated, 'impact statement', $thumbnail, new ArraySequence([new Paragraph('text')])
                ),
                [],
                [
                    'id' => 'id',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                        'orcid' => '0000-0002-1825-0097',
                        'cv' => [
                            [
                                'date' => 'date',
                                'text' => 'text',
                            ],
                        ],
                    ],
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'impact statement',
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
            'minimum' => [
                new Interview('id',
                    new Interviewee(new PersonDetails('preferred name', 'index name'), new EmptySequence()),
                    'title', $published, null, null, null, new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => 'id',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                    ],
                    'title' => 'title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                $interview = new Interview('interview1',
                    new Interviewee(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'),
                        new ArraySequence([new IntervieweeCvLine('date', 'text')])), 'Interview 1 title', $published, $updated,
                    'Interview 1 impact statement', $thumbnail, new ArraySequence([new Paragraph('Interview interview1 text')])
                ),
                ['snippet' => true, 'type' => true],
                [
                    'id' => 'interview1',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                        'orcid' => '0000-0002-1825-0097',
                    ],
                    'title' => 'Interview 1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'Interview 1 impact statement',
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
                    'type' => 'interview',
                ],
                function (ApiTestCase $test) {
                    $test->mockInterviewCall('interview1', true);
                },
            ],
            'minimum snippet' => [
                $interview = new Interview('interview1',
                    new Interviewee(new PersonDetails('preferred name', 'index name'), new EmptySequence()),
                    'Interview 1 title', $published, null, null, null, new ArraySequence([new Paragraph('Interview interview1 text')])
                ),
                ['snippet' => true],
                [
                    'id' => 'interview1',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                    ],
                    'title' => 'Interview 1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                ],
                function (ApiTestCase $test) {
                    $test->mockInterviewCall('interview1');
                },
            ],
        ];
    }

    protected function class() : string
    {
        return Interview::class;
    }

    protected function samples() : string
    {
        return __DIR__.'/../../vendor/elife/api/dist/samples/interview/v2';
    }
}
