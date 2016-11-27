<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Serializer\LabsExperimentNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class LabsExperimentNormalizerTest extends ApiTestCase
{
    /** @var LabsExperimentNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new LabsExperimentNormalizer(new LabsClient($this->getHttpClient()));
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
    public function it_can_normalize_labs_experiments($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $thumbnail = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, rejection_for('No banner'),
            $thumbnail, new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        return [
            'Labs experiment' => [$labsExperiment, null, true],
            'Labs experiment with format' => [$labsExperiment, 'foo', true],
            'non-Labs experiment' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_labs_experiments(LabsExperiment $labsExperiment, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($labsExperiment, null, $context));
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
    public function it_can_denormalize_labs_experiments($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'Labs experiment' => [[], LabsExperiment::class, [], true],
            'Labs experiment by type' => [['type' => 'labs-experiment'], Model::class, [], true],
            'non-Labs experiment' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_labs_experiments(
        LabsExperiment $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, LabsExperiment::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable('now', new DateTimeZone('Z'));
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
            'complete' => [
                new LabsExperiment(1, 'title', $date, 'impact statement', promise_for($banner), $thumbnail,
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'number' => 1,
                    'title' => 'title',
                    'published' => $date->format('Y-m-d\TH:i:s\Z'),
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
                new LabsExperiment(1, 'title', $date, null, promise_for($banner), $thumbnail,
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'number' => 1,
                    'title' => 'title',
                    'published' => $date->format('Y-m-d\TH:i:s\Z'),
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
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new LabsExperiment(1, 'Labs experiment 1 title', $date, 'Labs experiment 1 impact statement',
                    promise_for($banner), $thumbnail, new ArraySequence([new Paragraph('Labs experiment 1 text')])),
                ['snippet' => true, 'type' => true],
                [
                    'number' => 1,
                    'title' => 'Labs experiment 1 title',
                    'published' => $date->format('Y-m-d\TH:i:s\Z'),
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
                    'impactStatement' => 'Labs experiment 1 impact statement',
                    'type' => 'labs-experiment',
                ],
                function (ApiTestCase $test) {
                    $test->mockLabsExperimentCall(1, true);
                },
            ],
            'minimum snippet' => [
                new LabsExperiment(1, 'Labs experiment 1 title', $date, null, promise_for($banner), $thumbnail,
                    new ArraySequence([new Paragraph('Labs experiment 1 text')])),
                ['snippet' => true],
                [
                    'number' => 1,
                    'title' => 'Labs experiment 1 title',
                    'published' => $date->format('Y-m-d\TH:i:s\Z'),
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
                ],
                function (ApiTestCase $test) {
                    $test->mockLabsExperimentCall(1);
                },
            ],
        ];
    }
}
