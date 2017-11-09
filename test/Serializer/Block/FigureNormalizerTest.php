<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figure;
use eLife\ApiSdk\Model\Block\FigureAsset;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Image as ImageModel;
use eLife\ApiSdk\Serializer\AssetFileNormalizer;
use eLife\ApiSdk\Serializer\Block\FigureNormalizer;
use eLife\ApiSdk\Serializer\Block\ImageNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\TableNormalizer;
use eLife\ApiSdk\Serializer\Block\VideoNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer as ImageModelNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class FigureNormalizerTest extends TestCase
{
    /** @var FigureNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new FigureNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new AssetFileNormalizer(),
            new FigureNormalizer(),
            new FileNormalizer(),
            new ImageNormalizer(),
            new ImageModelNormalizer(),
            new ParagraphNormalizer(),
            new TableNormalizer(),
            new VideoNormalizer(),
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
    public function it_can_normalize_figures($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $assets = new ArraySequence([
            new FigureAsset(null, 'label', new EmptySequence(), new Image(null, null, new EmptySequence(), Builder::for(ImageModel::class)->__invoke())),
        ]);
        $figure = new Figure(...$assets);

        return [
            'figure' => [$figure, null, true],
            'figure with format' => [$figure, 'foo', true],
            'non-figure' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_figures(Figure $figure, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($figure));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Figure(
                    new FigureAsset(
                        '10.1000/182.1',
                        'label1',
                        new ArraySequence([
                            new AssetFile(
                                '10.1000/182.2',
                                'id2',
                                'label2',
                                'title2',
                                new ArraySequence([new Paragraph('paragraph2')]),
                                new ArraySequence(['attribution2']),
                                new File('text/plain', 'http://www.example.com/image2.txt', 'image2.txt')
                            ),
                        ]),
                        new Image(
                            'id3',
                            'title3',
                            new ArraySequence([new Paragraph('caption3')]),
                            Builder::for(ImageModel::class)
                                ->withSequenceOfAttribution('attribution3')
                                ->__invoke()
                        )
                    )
                ),
                [
                    'type' => 'figure',
                    'assets' => [
                        [
                            'type' => 'image',
                            'image' => [
                                'alt' => '',
                                'uri' => 'https://iiif.elifesciences.org/example.jpg',
                                'source' => [
                                    'mediaType' => 'image/jpeg',
                                    'uri' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
                                    'filename' => 'example.jpg',
                                ],
                                'size' => [
                                    'width' => 1000,
                                    'height' => 500,
                                ],
                                'attribution' => [
                                    'attribution3',
                                ],
                            ],
                            'id' => 'id3',
                            'title' => 'title3',
                            'caption' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'caption3',
                                ],
                            ],
                            'doi' => '10.1000/182.1',
                            'label' => 'label1',
                            'sourceData' => [
                                [
                                    'mediaType' => 'text/plain',
                                    'uri' => 'http://www.example.com/image2.txt',
                                    'filename' => 'image2.txt',
                                    'id' => 'id2',
                                    'label' => 'label2',
                                    'doi' => '10.1000/182.2',
                                    'title' => 'title2',
                                    'caption' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'paragraph2',
                                        ],
                                    ],
                                    'attribution' => [
                                        'attribution2',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Figure(
                    new FigureAsset(null, 'label', new EmptySequence(), new Image(null, null, new EmptySequence(), Builder::for(ImageModel::class)->__invoke()))
                ),
                [
                    'type' => 'figure',
                    'assets' => [
                        [
                            'type' => 'image',
                            'image' => [
                                'alt' => '',
                                'uri' => 'https://iiif.elifesciences.org/example.jpg',
                                'source' => [
                                    'mediaType' => 'image/jpeg',
                                    'uri' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
                                    'filename' => 'example.jpg',
                                ],
                                'size' => [
                                    'width' => 1000,
                                    'height' => 500,
                                ],
                            ],
                            'label' => 'label',
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
    public function it_can_denormalize_figures($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'figure' => [[], Figure::class, [], true],
            'block that is an figure' => [['type' => 'figure'], Block::class, [], true],
            'block that isn\'t an figure' => [['type' => 'foo'], Block::class, [], false],
            'non-figure' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_figures(array $json, Figure $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Figure::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'figure',
                    'assets' => [
                        [
                            'type' => 'image',
                            'image' => [
                                'alt' => '',
                                'uri' => 'https://iiif.elifesciences.org/example.jpg',
                                'attribution' => [
                                    'attribution3',
                                ],
                                'source' => [
                                    'mediaType' => 'image/jpeg',
                                    'uri' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
                                    'filename' => 'example.jpg',
                                ],
                                'size' => [
                                    'width' => 1000,
                                    'height' => 500,
                                ],
                            ],
                            'id' => 'id3',
                            'title' => 'title3',
                            'caption' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'caption3',
                                ],
                            ],
                            'doi' => '10.1000/182.1',
                            'label' => 'label1',
                            'sourceData' => [
                                [
                                    'mediaType' => 'text/plain',
                                    'uri' => 'http://www.example.com/image2.txt',
                                    'filename' => 'image2.txt',
                                    'doi' => '10.1000/182.2',
                                    'id' => 'id2',
                                    'label' => 'label2',
                                    'title' => 'title2',
                                    'caption' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'paragraph2',
                                        ],
                                    ],
                                    'attribution' => [
                                        'attribution2',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                new Figure(
                    new FigureAsset(
                        '10.1000/182.1',
                        'label1',
                        new ArraySequence([
                            new AssetFile(
                                '10.1000/182.2',
                                'id2',
                                'label2',
                                'title2',
                                new ArraySequence([new Paragraph('paragraph2')]),
                                new ArraySequence(['attribution2']),
                                new File('text/plain', 'http://www.example.com/image2.txt', 'image2.txt')
                            ),
                        ]),
                        new Image(
                            'id3',
                            'title3',
                            new ArraySequence([new Paragraph('caption3')]),
                            Builder::for(ImageModel::class)
                                ->withSequenceOfAttribution('attribution3')
                                ->__invoke()
                        )
                    )
                ),
            ],
            'minimum' => [
                [
                    'type' => 'figure',
                    'assets' => [
                        [
                            'type' => 'image',
                            'label' => 'label',
                            'image' => [
                                'alt' => '',
                                'uri' => 'https://iiif.elifesciences.org/example.jpg',
                                'source' => [
                                    'mediaType' => 'image/jpeg',
                                    'uri' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
                                    'filename' => 'example.jpg',
                                ],
                                'size' => [
                                    'width' => 1000,
                                    'height' => 500,
                                ],
                            ],
                        ],
                    ],
                ],
                new Figure(
                    new FigureAsset(null, 'label', new EmptySequence(), new Image(null, null, new EmptySequence(), Builder::for(ImageModel::class)->__invoke()))
                ),
            ],
        ];
    }
}
