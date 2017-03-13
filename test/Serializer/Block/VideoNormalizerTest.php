<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Box;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\VideoNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class VideoNormalizerTest extends TestCase
{
    /** @var VideoNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new VideoNormalizer();

        new NormalizerAwareSerializer([
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
    public function it_can_normalize_videos($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100);

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
                new Video('10.1000/182', 'id', 'label', 'title', new ArraySequence([new Paragraph('caption')]),
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')],
                    'http://www.example.com/image.jpeg', 200, 100, true, true,
                    [
                        new File('10.1000/182.1', 'id2', 'label2', 'title2', new ArraySequence([new Paragraph('paragraph2')]),
                            'text/plain', 'http://www.example.com/data.txt', 'data.txt'),
                    ]),
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
                    'autoplay' => true,
                    'loop' => true,
                    'sourceData' => [
                        [
                            'mediaType' => 'text/plain',
                            'uri' => 'http://www.example.com/data.txt',
                            'filename' => 'data.txt',
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
                new Video(null, null, null, null, new EmptySequence(),
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
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Video::class));
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
                    'autoplay' => true,
                    'loop' => true,
                    'sourceData' => [
                        [
                            'mediaType' => 'text/plain',
                            'uri' => 'http://www.example.com/data.txt',
                            'filename' => 'data.txt',
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
                new Video('10.1000/182', 'id', 'label', 'title', new ArraySequence([new Paragraph('caption')]),
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')],
                    'http://www.example.com/image.jpeg', 200, 100, true, true,
                    [
                        new File('10.1000/182.1', 'id2', 'label2', 'title2', new ArraySequence([new Paragraph('paragraph2')]),
                            'text/plain', 'http://www.example.com/data.txt', 'data.txt'),
                    ]),
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
                new Video(null, null, null, null, new EmptySequence(),
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')], null, 200, 100),
            ],
        ];
    }
}
