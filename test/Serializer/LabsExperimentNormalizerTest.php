<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\LabsExperimentNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class LabsExperimentNormalizerTest extends TestCase
{
    /** @var LabsExperimentNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new LabsExperimentNormalizer();

        new Serializer([
            $this->normalizer,
            new ImageNormalizer(),
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
    public function it_can_normalize_labs_experiments($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable(), null, $image,
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
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
            'non-Labs experiment' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_labs_experiments(LabsExperiment $expected, array $context, array $json)
    {
        $actual = $this->normalizer->denormalize($json, LabsExperiment::class, null, $context);

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
        $date = new DateTimeImmutable();
        $image = new Image('alt', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);

        return [
            'complete' => [
                new LabsExperiment(1, 'title', $date, 'impact statement', $image,
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'number' => 1,
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'image' => ['alt' => 'alt', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
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
                new LabsExperiment(1, 'title', $date, null, $image, new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'number' => 1,
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'image' => ['alt' => 'alt', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new LabsExperiment(1, 'title', $date, 'impact statement', $image,
                    new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))),
                ['snippet' => true],
                [
                    'number' => 1,
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'image' => ['alt' => 'alt', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
                    'impactStatement' => 'impact statement',
                ],
            ],
            'minimum snippet' => [
                new LabsExperiment(1, 'title', $date, null, $image,
                    new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))),
                ['snippet' => true],
                [
                    'number' => 1,
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'image' => ['alt' => 'alt', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
                ],
            ],
        ];
    }
}
