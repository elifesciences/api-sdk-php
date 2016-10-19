<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;

final class SubjectNormalizerTest extends ApiTestCase
{
    /** @var SubjectNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new SubjectNormalizer(new ApiSdk($this->getHttpClient()));

        new Serializer([$this->normalizer, new ImageNormalizer()]);
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
    public function it_can_normalize_subjects($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $subject = new Subject('id', 'name', promise_for(null), promise_for($image));

        return [
            'subject' => [$subject, null, true],
            'subject with format' => [$subject, 'foo', true],
            'non-subject' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_subjects(Subject $subject, array $context, array $expected)
    {
        if (!empty($context['snippet'])) {
            $this->mockSubjectCall(1);
        }

        $this->assertSame($expected, $this->normalizer->normalize($subject, null, $context));
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
    public function it_can_denormalize_subjects($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'subject' => [[], Subject::class, [], true],
            'non-subject' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_subjects(Subject $expected, array $context, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Subject::class, null, $context);

        if (!empty($context['snippet'])) {
            $this->mockSubjectCall(1);
        }

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $image = new Image('', [
            new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900']),
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
            'complete' => [
                new Subject('subject1', 'Subject 1 name', promise_for('Subject 1 impact statement'),
                    promise_for($image)),
                [],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                    'image' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                '900' => 'https://placehold.it/900x450',
                                '1800' => 'https://placehold.it/1800x900',
                            ],
                            '16:9' => [
                                '250' => 'https://placehold.it/250x141',
                                '500' => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                '70' => 'https://placehold.it/70x70',
                                '140' => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
                    'impactStatement' => 'Subject 1 impact statement',
                ],
            ],
            'minimum' => [
                new Subject('subject1', 'Subject 1 name', promise_for(null), promise_for($image)),
                [],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                    'image' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                '900' => 'https://placehold.it/900x450',
                                '1800' => 'https://placehold.it/1800x900',
                            ],
                            '16:9' => [
                                '250' => 'https://placehold.it/250x141',
                                '500' => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                '70' => 'https://placehold.it/70x70',
                                '140' => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
                ],
            ],
            'snippet' => [
                new Subject('subject1', 'Subject 1 name', promise_for('Subject 1 impact statement'),
                    promise_for($image)),
                ['snippet' => true],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                ],
            ],
        ];
    }
}
