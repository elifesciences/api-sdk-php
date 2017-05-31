<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Excerpt;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Serializer\Block\ExcerptNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ExcerptNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var ExcerptNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ExcerptNormalizer();

        new NormalizerAwareSerializer([
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
    public function it_can_normalize_excerpts($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $excerpt = new Excerpt(new ArraySequence([new Paragraph('foo')]));

        return [
            'excerpt' => [$excerpt, null, true],
            'excerpt with format' => [$excerpt, 'foo', true],
            'non-excerpt' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_excerpts(Excerpt $excerpt, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($excerpt));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Excerpt(new ArraySequence([new Paragraph('paragraph')]), 'cite'),
                [
                    'type' => 'excerpt',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'cite' => 'cite',
                ],
            ],
            'minimum' => [
                new Excerpt(new ArraySequence([new Paragraph('paragraph')])),
                [
                    'type' => 'excerpt',
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
    public function it_can_denormalize_excerpts($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'excerpt' => [[], Excerpt::class, [], true],
            'block that is a excerpt' => [['type' => 'excerpt'], Block::class, [], true],
            'block that isn\'t a excerpt' => [['type' => 'foo'], Block::class, [], false],
            'non-excerpt' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_excerpts(array $json, Excerpt $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Excerpt::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'excerpt',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'cite' => 'cite',
                ],
                new Excerpt(new ArraySequence([new Paragraph('paragraph')]), 'cite'),
            ],
            'minimum' => [
                [
                    'type' => 'excerpt',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                ],
                new Excerpt(new ArraySequence([new Paragraph('paragraph')])),
            ],
        ];
    }
}
