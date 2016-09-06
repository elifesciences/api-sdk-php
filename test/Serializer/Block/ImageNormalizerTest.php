<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Serializer\Block\ImageNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
        $image = new Image('http://www.example.com/image.jpg', '');

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
                $image = new Image('http://www.example.com/image.jpg', 'alt', 'caption'),
                [
                    'type' => 'image',
                    'uri' => 'http://www.example.com/image.jpg',
                    'alt' => 'alt',
                    'caption' => 'caption',
                ],
            ],
            'minimum' => [
                $image = new Image('http://www.example.com/image.jpg', ''),
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
                    'uri' => 'http://www.example.com/image.jpg',
                    'alt' => 'alt',
                    'caption' => 'caption',
                ],
                $image = new Image('http://www.example.com/image.jpg', 'alt', 'caption'),
            ],
            'minimum' => [
                [
                    'type' => 'image',
                    'uri' => 'http://www.example.com/image.jpg',
                    'alt' => '',
                ],
                $image = new Image('http://www.example.com/image.jpg', ''),
            ],
        ];
    }
}
