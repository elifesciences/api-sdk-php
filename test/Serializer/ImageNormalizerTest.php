<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Serializer\ImageNormalizer;
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
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);

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
                new Image('alt', [
                    new ImageSize('2:1',
                        [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900']),
                    new ImageSize('4:1',
                        [900 => 'https://placehold.it/900x225', 1800 => 'https://placehold.it/1800x450']),
                ]),
                [
                    'alt' => 'alt',
                    'sizes' => [
                        '2:1' => [
                            900 => 'https://placehold.it/900x450',
                            1800 => 'https://placehold.it/1800x900',
                        ],
                        '4:1' => [900 => 'https://placehold.it/900x225', 1800 => 'https://placehold.it/1800x450'],
                    ],
                ],
            ],
            'minimum' => [
                new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]),
                ['alt' => '', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
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
                    'alt' => 'alt',
                    'sizes' => [
                        '2:1' => [
                            900 => 'https://placehold.it/900x450',
                            1800 => 'https://placehold.it/1800x900',
                        ],
                        '4:1' => [900 => 'https://placehold.it/900x225', 1800 => 'https://placehold.it/1800x450'],
                    ],
                ],
                new Image('alt', [
                    new ImageSize('2:1',
                        [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900']),
                    new ImageSize('4:1',
                        [900 => 'https://placehold.it/900x225', 1800 => 'https://placehold.it/1800x450']),
                ]),
            ],
            'minimum' => [
                ['alt' => '', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
                new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]),
            ],
        ];
    }
}
