<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Box;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Serializer\Block\BoxNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class BoxNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var BoxNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new BoxNormalizer();

        new Serializer([
            $this->normalizer,
            new ParagraphNormalizer(),
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
    public function it_can_normalize_boxes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $box = new Box(null, null, null, 'foo', []);

        return [
            'box' => [$box, null, true],
            'box with format' => [$box, 'foo', true],
            'non-box' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_boxes(Box $box, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($box));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Box('10.1000/182', 'id', 'label', 'title', [new Paragraph('paragraph')]),
                [
                    'type' => 'box',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'doi' => '10.1000/182',
                    'id' => 'id',
                    'label' => 'label',
                ],
            ],
            'minimum' => [
                new Box(null, null, null, 'title', [new Paragraph('paragraph')]),
                [
                    'type' => 'box',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                ],
            ],
        ];
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
    public function it_can_denormalize_boxes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'box' => [[], Box::class, [], true],
            'block that is a box' => [['type' => 'box'], Block::class, [], true],
            'block that isn\'t a box' => [['type' => 'foo'], Block::class, [], false],
            'non-box' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_boxes(array $json, Box $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Box::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'box',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'doi' => '10.1000/182',
                    'id' => 'id',
                    'label' => 'label',
                ],
                new Box('10.1000/182', 'id', 'label', 'title', [new Paragraph('paragraph')]),
            ],
            'minimum' => [
                [
                    'type' => 'box',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                ],
                new Box(null, null, null, 'title', [new Paragraph('paragraph')]),
            ],
        ];
    }
}
