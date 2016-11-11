<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\File;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Block\ImageFile;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Serializer\Block\FileNormalizer;
use eLife\ApiSdk\Serializer\Block\ImageNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class ImageNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var ImageNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ImageNormalizer();

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
    public function it_can_normalize_images($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $image = new Image(
            new ImageFile(null, null, null, null, [], '', 'http://www.example.com/image.jpg', [], [])
        );

        return [
            'image' => [$image, null, true],
            'image with format' => [$image, 'foo', true],
            'non-image' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_images(Image $image, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($image));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Image(
                    new ImageFile('10.1000/182', 'id1', 'label1', 'title1', [new Paragraph('paragraph1')],
                        'alt1', 'http://www.example.com/image1.jpg', ['attribution1'], [
                            new File('10.1000/182.1', 'id2', 'label2', 'title2', [new Paragraph('paragraph2')],
                                'text/plain', 'http://www.example.com/image1.txt', 'image1.txt'),
                        ]),
                    new ImageFile('10.1000/182.2', 'id3', 'label3', 'title3', [new Paragraph('paragraph3')],
                        'alt2', 'http://www.example.com/image2.jpg', ['attribution2'], [
                            new File('10.1000/182.3', 'id4', 'label4', 'title4', [new Paragraph('paragraph4')],
                                'text/plain', 'http://www.example.com/image2.txt', 'image2.txt'),
                        ])
                ),
                [
                    'type' => 'image',
                    'uri' => 'http://www.example.com/image1.jpg',
                    'alt' => 'alt1',
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
                    'attribution' => [
                        'attribution1',
                    ],
                    'sourceData' => [
                        [
                            'mediaType' => 'text/plain',
                            'uri' => 'http://www.example.com/image1.txt',
                            'filename' => 'image1.txt',
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
                    'supplements' => [
                        [
                            'uri' => 'http://www.example.com/image2.jpg',
                            'alt' => 'alt2',
                            'doi' => '10.1000/182.2',
                            'id' => 'id3',
                            'label' => 'label3',
                            'title' => 'title3',
                            'caption' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'paragraph3',
                                ],
                            ],
                            'attribution' => [
                                'attribution2',
                            ],
                            'sourceData' => [
                                [
                                    'mediaType' => 'text/plain',
                                    'uri' => 'http://www.example.com/image2.txt',
                                    'filename' => 'image2.txt',
                                    'doi' => '10.1000/182.3',
                                    'id' => 'id4',
                                    'label' => 'label4',
                                    'title' => 'title4',
                                    'caption' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'paragraph4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Image(
                    new ImageFile(null, null, null, null, [], '', 'http://www.example.com/image.jpg', [], [])
                ),
                [
                    'type' => 'image',
                    'uri' => 'http://www.example.com/image.jpg',
                    'alt' => '',
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
    public function it_can_denormalize_images($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'image' => [[], Image::class, [], true],
            'block that is an image' => [['type' => 'image'], Block::class, [], true],
            'block that isn\'t an image' => [['type' => 'foo'], Block::class, [], false],
            'non-image' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_images(array $json, Image $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Image::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'image',
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
                    'alt' => 'alt1',
                    'uri' => 'http://www.example.com/image1.jpg',
                    'attribution' => [
                        'attribution1',
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
                            'uri' => 'http://www.example.com/image1.txt',
                            'filename' => 'image1.txt',
                        ],
                    ],
                    'supplements' => [
                        [
                            'doi' => '10.1000/182.2',
                            'id' => 'id3',
                            'label' => 'label3',
                            'title' => 'title3',
                            'caption' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'paragraph3',
                                ],
                            ],
                            'alt' => 'alt2',
                            'uri' => 'http://www.example.com/image2.jpg',
                            'attribution' => [
                                'attribution2',
                            ],
                            'sourceData' => [
                                [
                                    'doi' => '10.1000/182.3',
                                    'id' => 'id4',
                                    'label' => 'label4',
                                    'title' => 'title4',
                                    'caption' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'paragraph4',
                                        ],
                                    ],
                                    'mediaType' => 'text/plain',
                                    'uri' => 'http://www.example.com/image2.txt',
                                    'filename' => 'image2.txt',
                                ],
                            ],
                        ],
                    ],
                ],
                new Image(
                    new ImageFile('10.1000/182', 'id1', 'label1', 'title1', [new Paragraph('paragraph1')],
                        'alt1', 'http://www.example.com/image1.jpg', ['attribution1'], [
                            new File('10.1000/182.1', 'id2', 'label2', 'title2', [new Paragraph('paragraph2')],
                                'text/plain', 'http://www.example.com/image1.txt', 'image1.txt'),
                        ]),
                    new ImageFile('10.1000/182.2', 'id3', 'label3', 'title3', [new Paragraph('paragraph3')],
                        'alt2', 'http://www.example.com/image2.jpg', ['attribution2'], [
                            new File('10.1000/182.3', 'id4', 'label4', 'title4', [new Paragraph('paragraph4')],
                                'text/plain', 'http://www.example.com/image2.txt', 'image2.txt'),
                        ])
                ),
            ],
            'minimum' => [
                [
                    'type' => 'image',
                    'uri' => 'http://www.example.com/image.jpg',
                    'alt' => '',
                ],
                new Image(
                    new ImageFile(null, null, null, null, [], '', 'http://www.example.com/image.jpg', [], [])
                ),
            ],
        ];
    }
}
