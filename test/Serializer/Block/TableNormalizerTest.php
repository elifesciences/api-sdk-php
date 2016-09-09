<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\File;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Table;
use eLife\ApiSdk\Serializer\Block\FileNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\TableNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class TableNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var TableNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new TableNormalizer();

        new Serializer([
            $this->normalizer,
            new FileNormalizer(),
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
    public function it_can_normalize_tables($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $table = new Table(null, null, null, null, [], ['<table></table>'], [], []);

        return [
            'table' => [$table, null, true],
            'table with format' => [$table, 'foo', true],
            'non-table' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_tables(Table $table, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($table));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Table('10.1000/182', 'id1', 'label1', 'title1', [new Paragraph('paragraph1')], ['<table></table>'],
                    [new Paragraph('footer')], [
                        new File('10.1000/182.1', 'id2', 'label2', 'title2', [new Paragraph('paragraph2')],
                            'text/plain', 'http://www.example.com/data.txt'),
                    ]),
                [
                    'type' => 'table',
                    'tables' => ['<table></table>'],
                    'doi' => '10.1000/182',
                    'id' => 'id1',
                    'label' => 'label1',
                    'title' => 'title1',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph1',
                        ],
                    ],
                    'footer' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'footer',
                        ],
                    ],
                    'sourceData' => [
                        [
                            'mediaType' => 'text/plain',
                            'uri' => 'http://www.example.com/data.txt',
                            'doi' => '10.1000/182.1',
                            'id' => 'id2',
                            'label' => 'label2',
                            'title' => 'title2',
                            'caption' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'paragraph2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Table(null, null, null, null, [], ['<table></table>'], [], []),
                [
                    'type' => 'table',
                    'tables' => ['<table></table>'],
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
    public function it_can_denormalize_tables($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'table' => [[], Table::class, [], true],
            'block that is a table' => [['type' => 'table'], Block::class, [], true],
            'block that isn\'t a table' => [['type' => 'foo'], Block::class, [], false],
            'non-table' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_tables(array $json, Table $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Table::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'table',
                    'doi' => '10.1000/182',
                    'id' => 'id1',
                    'label' => 'label1',
                    'title' => 'title1',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'caption',
                        ],
                    ],
                    'tables' => ['<table></table>'],
                    'footer' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'footer',
                        ],
                    ],
                    'sourceData' => [
                        [
                            'doi' => '10.1000/182.1',
                            'id' => 'id2',
                            'label' => 'label2',
                            'title' => 'title2',
                            'caption' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'paragraph2',
                                ],
                            ],
                            'mediaType' => 'text/plain',
                            'uri' => 'http://www.example.com/data.txt',
                        ],
                    ],
                ],
                new Table('10.1000/182', 'id1', 'label1', 'title1', [new Paragraph('caption')], ['<table></table>'],
                    [new Paragraph('footer')], [
                        new File('10.1000/182.1', 'id2', 'label2', 'title2', [new Paragraph('paragraph2')],
                            'text/plain', 'http://www.example.com/data.txt'),
                    ]),
            ],
            'minimum' => [
                [
                    'type' => 'table',
                    'tables' => ['<table></table>'],
                ],
                new Table(null, null, null, null, [], ['<table></table>'], [], []),
            ],
        ];
    }
}
