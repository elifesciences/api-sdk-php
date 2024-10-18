<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Quote;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\QuoteNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class QuoteNormalizerTest extends TestCase
{
    /** @var QuoteNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new QuoteNormalizer();

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
    public function it_can_normalize_quotes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $quote = new Quote([new Paragraph('foo')]);

        return [
            'quote' => [$quote, null, true],
            'quote with format' => [$quote, 'foo', true],
            'non-quote' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_quotes(Quote $quote, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($quote));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Quote([new Paragraph('paragraph')], 'cite'),
                [
                    'type' => 'quote',
                    'text' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'cite' => 'cite',
                ],
            ],
            'minimum' => [
                new Quote([new Paragraph('paragraph')]),
                [
                    'type' => 'quote',
                    'text' => [
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
    public function it_can_denormalize_quotes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'quote' => [[], Quote::class, [], true],
            'block that is a quote' => [['type' => 'quote'], Block::class, [], true],
            'block that isn\'t a quote' => [['type' => 'foo'], Block::class, [], false],
            'non-quote' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_quotes(array $json, Quote $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Quote::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'quote',
                    'text' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'cite' => 'cite',
                ],
                new Quote([new Paragraph('paragraph')], 'cite'),
            ],
            'minimum' => [
                [
                    'type' => 'quote',
                    'text' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                ],
                new Quote([new Paragraph('paragraph')]),
            ],
        ];
    }
}
