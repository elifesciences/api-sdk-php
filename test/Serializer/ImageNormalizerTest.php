<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
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

        new NormalizerAwareSerializer([$this->normalizer, new FileNormalizer()]);
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
        $image = Builder::dummy(Image::class);

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
            'non-image' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_images(Image $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Image::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');

        return [
            'complete' => [
                new Image('alt', 'https://iiif.elifesciences.org/example.jpg', $file, 1000, 500, 25, 75),
                [
                    'alt' => 'alt',
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
                    'focalPoint' => [
                        'x' => 25,
                        'y' => 75,
                    ],
                ],
            ],
            'minimum' => [
                new Image('', 'https://iiif.elifesciences.org/example.jpg', $file, 1000, 500, 50, 50),
                [
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
        ];
    }
}
