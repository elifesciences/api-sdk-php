<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Table;
use eLife\ApiSdk\Model\Footnote;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\TableNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class TableNormalizerTest extends TestCase
{
    /** @var TableNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new TableNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new FileNormalizer(),
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
    public function it_can_normalize_tables($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $table = new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>'], [], []);

        return [
            'table' => [$table, null, true],
            'table with format' => [$table, 'foo', true],
            'non-table' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_tables(Table $table, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($table));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Table('id1', 'title1', new ArraySequence([new Paragraph('paragraph1')]), new ArraySequence(['attribution']), ['<table></table>'],
                    [new Footnote('fn1', '#', new ArraySequence([new Paragraph('footnote 1')])), new Footnote(null, null, new ArraySequence([new Paragraph('footnote 2')]))]),
                [
                    'type' => 'table',
                    'tables' => ['<table></table>'],
                    'id' => 'id1',
                    'title' => 'title1',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph1',
                        ],
                    ],
                    'attribution' => [
                        'attribution',
                    ],
                    'footnotes' => [
                        [
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'footnote 1',
                                ],
                            ],
                            'id' => 'fn1',
                            'label' => '#',
                        ],
                        [
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'footnote 2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>']),
                [
                    'type' => 'table',
                    'tables' => ['<table></table>'],
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
    public function it_can_denormalize_tables($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'table' => [[], Table::class, [], true],
            'block that is a table' => [['type' => 'table'], Block::class, [], true],
            'block that isn\'t a table' => [['type' => 'foo'], Block::class, [], false],
            'non-table' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_tables(array $json, Table $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Table::class));
    }

    public static function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'table',
                    'id' => 'id1',
                    'title' => 'title1',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph1',
                        ],
                    ],
                    'attribution' => [
                        'attribution',
                    ],
                    'tables' => ['<table></table>'],
                    'footnotes' => [
                        [
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'footnote 1',
                                ],
                            ],
                            'id' => 'fn1',
                            'label' => '#',
                        ],
                        [
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'footnote 2',
                                ],
                            ],
                        ],
                    ],
                ],
                new Table('id1', 'title1', new ArraySequence([new Paragraph('paragraph1')]), new ArraySequence(['attribution']), ['<table></table>'],
                    [new Footnote('fn1', '#', new ArraySequence([new Paragraph('footnote 1')])), new Footnote(null, null, new ArraySequence([new Paragraph('footnote 2')]))]),
            ],
            'minimum' => [
                [
                    'type' => 'table',
                    'tables' => ['<table></table>'],
                ],
                new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>']),
            ],
        ];
    }
}
