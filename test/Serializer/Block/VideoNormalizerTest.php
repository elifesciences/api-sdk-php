<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Box;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\VideoNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class VideoNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var VideoNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new VideoNormalizer();

        new Serializer([
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
    public function it_can_normalize_videos($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, [], $sources, '', 200, 100);

        return [
            'video' => [$video, null, true],
            'video with format' => [$video, 'foo', true],
            'non-box' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_videos(Video $video, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($video));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Video('10.1000/182', 'id', 'label', 'title', [new Paragraph('caption')],
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')],
                    'http://www.example.com/image.jpeg', 200, 100),
                [
                    'type' => 'video',
                    'sources' => [
                        [
                            'mediaType' => 'video/mpeg',
                            'uri' => 'http://www.example.com/video.mpeg',
                        ],
                    ],
                    'width' => 200,
                    'height' => 100,
                    'doi' => '10.1000/182',
                    'id' => 'id',
                    'label' => 'label',
                    'title' => 'title',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'caption',
                        ],
                    ],
                    'image' => 'http://www.example.com/image.jpeg',
                ],
            ],
            'minimum' => [
                new Video(null, null, null, null, [],
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')], null, 200, 100),
                [
                    'type' => 'video',
                    'sources' => [
                        [
                            'mediaType' => 'video/mpeg',
                            'uri' => 'http://www.example.com/video.mpeg',
                        ],
                    ],
                    'width' => 200,
                    'height' => 100,
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
    public function it_can_denormalize_videos($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'video' => [[], Video::class, [], true],
            'block that is a video' => [['type' => 'video'], Block::class, [], true],
            'block that isn\'t a video' => [['type' => 'foo'], Block::class, [], false],
            'non-video' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_videos(array $json, Video $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Video::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'video',
                    'doi' => '10.1000/182',
                    'id' => 'id',
                    'label' => 'label',
                    'title' => 'title',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'caption',
                        ],
                    ],
                    'sources' => [
                        [
                            'mediaType' => 'video/mpeg',
                            'uri' => 'http://www.example.com/video.mpeg',
                        ],
                    ],
                    'image' => 'http://www.example.com/image.jpeg',
                    'width' => 200,
                    'height' => 100,
                ],
                new Video('10.1000/182', 'id', 'label', 'title', [new Paragraph('caption')],
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')],
                    'http://www.example.com/image.jpeg', 200, 100),
            ],
            'minimum' => [
                [
                    'type' => 'video',
                    'sources' => [
                        [
                            'mediaType' => 'video/mpeg',
                            'uri' => 'http://www.example.com/video.mpeg',
                        ],
                    ],
                    'width' => 200,
                    'height' => 100,
                ],
                new Video(null, null, null, null, [],
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')], null, 200, 100),
            ],
        ];
    }
}
