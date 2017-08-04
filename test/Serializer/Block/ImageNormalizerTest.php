<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image as ImageModel;
use eLife\ApiSdk\Serializer\Block\ImageNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer as ImageModelNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class ImageNormalizerTest extends TestCase
{
    /** @var ImageNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ImageNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new FileNormalizer(),
            new ImageModelNormalizer(),
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
        $image = new Image(null, null, new EmptySequence(), Builder::for(ImageModel::class)->__invoke());

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
                new Image('id1', 'title1', new ArraySequence([new Paragraph('paragraph1')]),
                    Builder::for(ImageModel::class)
                        ->withSequenceOfAttribution('attribution')
                        ->__invoke(), true),
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
                            'attribution',
                        ],
                    ],
                    'id' => 'id1',
                    'title' => 'title1',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph1',
                        ],
                    ],
                    'inline' => true,
                ],
            ],
            'minimum' => [
                new Image(null, null, new EmptySequence(), Builder::dummy(ImageModel::class)),
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
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Image::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'image',
                    'id' => 'id1',
                    'title' => 'title1',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph1',
                        ],
                    ],
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
                            'attribution',
                        ],
                    ],
                    'inline' => true,
                ],
                new Image('id1', 'title1', new ArraySequence([new Paragraph('paragraph1')]),
                    Builder::for(ImageModel::class)
                        ->withSequenceOfAttribution('attribution')
                        ->__invoke(), true),
            ],
            'minimum' => [
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
                ],
                new Image(null, null, new EmptySequence(), Builder::dummy(ImageModel::class)),
            ],
        ];
    }
}
