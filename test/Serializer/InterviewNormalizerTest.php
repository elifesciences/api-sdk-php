<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Serializer\InterviewNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\rejection_for;

final class InterviewNormalizerTest extends ApiTestCase
{
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
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null,
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
        return [
            'complete' => [
                $interview = new Interview('id',
                    new Interviewee(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'),
                        new ArraySequence([new IntervieweeCvLine('date', 'text')])), 'title',
                    $date = new DateTimeImmutable('now', new DateTimeZone('Z')), 'impact statement', new ArraySequence([new Paragraph('text')])
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
                    'published' => $date->format('Y-m-d\TH:i:s\Z'),
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
                new Interview('id',
                    new Interviewee(new PersonDetails('preferred name', 'index name'), new ArraySequence([])),
                    'title', $date, null, new ArraySequence([new Paragraph('text')])),
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
                    'published' => $date->format('Y-m-d\TH:i:s\Z'),
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
                        new ArraySequence([new IntervieweeCvLine('date', 'text')])), 'Interview 1 title', $date,
                    'Interview 1 impact statement', new ArraySequence([new Paragraph('Interview interview1 text')])
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
                    'published' => $date->format('Y-m-d\TH:i:s\Z'),
                    'impactStatement' => 'Interview 1 impact statement',
                    'type' => 'interview',
                ],
                function (ApiTestCase $test) {
                    $test->mockInterviewCall('interview1', true);
                },
            ],
            'minimum snippet' => [
                $interview = new Interview('interview1',
                    new Interviewee(new PersonDetails('preferred name', 'index name'), new ArraySequence([])),
                    'Interview 1 title', $date, null, new ArraySequence([new Paragraph('Interview interview1 text')])
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
                    'published' => $date->format('Y-m-d\TH:i:s\Z'),
                ],
                function (ApiTestCase $test) {
                    $test->mockInterviewCall('interview1');
                },
            ],
        ];
    }
}
