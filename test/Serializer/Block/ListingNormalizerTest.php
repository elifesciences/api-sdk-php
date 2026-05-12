<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Listing;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Serializer\Block\ListingNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PHPUnit\Framework\Attributes\Before as Before;

final class ListingNormalizerTest extends TestCase
{
    /** @var ListingNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new ListingNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new ParagraphNormalizer(),
        ]);
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_lists($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $list = new Listing(false, new ArraySequence(['foo']));

        return [
            'list' => [$list, null, true],
            'list with format' => [$list, 'foo', true],
            'non-list' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_lists(Listing $list, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($list));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Listing(Listing::PREFIX_NUMBER, new ArraySequence(['string', new ArraySequence([new Paragraph('paragraph')])])),
                [
                    'type' => 'list',
                    'prefix' => 'number',
                    'items' => [
                        'string',
                        [
                            [
                                'type' => 'paragraph',
                                'text' => 'paragraph',
                            ],
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Listing(Listing::PREFIX_NONE, new ArraySequence([new ArraySequence([new Paragraph('paragraph')])])),
                [
                    'type' => 'list',
                    'prefix' => 'none',
                    'items' => [
                        [
                            [
                                'type' => 'paragraph',
                                'text' => 'paragraph',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_lists($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'list' => [[], Listing::class, [], true],
            'block that is a list' => [['type' => 'list'], Block::class, [], true],
            'block that isn\'t a list' => [['type' => 'foo'], Block::class, [], false],
            'non-list' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_lists(array $json, Listing $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Listing::class));
    }

    public static function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'list',
                    'prefix' => 'number',
                    'items' => [
                        'string',
                        [
                            [
                                'type' => 'paragraph',
                                'text' => 'paragraph',
                            ],
                        ],
                    ],
                ],
                new Listing(Listing::PREFIX_NUMBER, new ArraySequence(['string', new ArraySequence([new Paragraph('paragraph')])])),
            ],
            'minimum' => [
                [
                    'type' => 'list',
                    'prefix' => 'none',
                    'items' => [
                        [
                            [
                                'type' => 'paragraph',
                                'text' => 'paragraph',
                            ],
                        ],
                    ],
                ],
                new Listing(Listing::PREFIX_NONE, new ArraySequence([new ArraySequence([new Paragraph('paragraph')])])),
            ],
        ];
    }
}
