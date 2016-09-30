<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\InterviewNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class InterviewNormalizerTest extends TestCase
{
    /** @var InterviewNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new InterviewNormalizer();

        new Serializer([
            $this->normalizer,
            new PersonNormalizer(),
            new Block\ParagraphNormalizer(),
        ]);
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
        $person = new Person('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseCollection(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable(), null,
            new PromiseCollection(rejection_for('Full interview should not be unwrapped'))
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
            'non-interview' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_interviews(Interview $expected, array $context, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Interview::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function denormalizeProvider() : array
    {
        $data = $this->normalizeProvider();

        unset($data['complete snippet']);
        unset($data['minimum snippet']);

        return $data;
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                $interview = new Interview('id',
                    new Interviewee(new Person('preferred name', 'index name', '0000-0002-1825-0097'),
                        new ArrayCollection([new IntervieweeCvLine('date', 'text')])), 'title',
                    $date = new DateTimeImmutable(), 'impact statement', new ArrayCollection([new Paragraph('text')])
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
                    'published' => $date->format(DATE_ATOM),
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
                new Interview('id', new Interviewee(new Person('preferred name', 'index name')), 'title', $date, null,
                    new ArrayCollection([new Paragraph('text')])),
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
                    'published' => $date->format(DATE_ATOM),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                $interview = new Interview('id',
                    new Interviewee(new Person('preferred name', 'index name', '0000-0002-1825-0097'),
                        new PromiseCollection(rejection_for('Full interviewee should not be unwrapped'))), 'title',
                    $date, 'impact statement',
                    new PromiseCollection(rejection_for('Full interview should not be unwrapped'))
                ),
                ['snippet' => true],
                [
                    'id' => 'id',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                        'orcid' => '0000-0002-1825-0097',
                    ],
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'impactStatement' => 'impact statement',
                ],
            ],
            'minimum snippet' => [
                $interview = new Interview('id',
                    new Interviewee(new Person('preferred name', 'index name')), 'title', $date, null,
                    new PromiseCollection(rejection_for('Full interview should not be unwrapped'))
                ),
                ['snippet' => true],
                [
                    'id' => 'id',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                    ],
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                ],
            ],
        ];
    }
}
