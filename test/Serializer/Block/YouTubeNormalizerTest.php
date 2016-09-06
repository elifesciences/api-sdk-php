<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\YouTube;
use eLife\ApiSdk\Serializer\Block\YouTubeNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class YouTubeNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var YouTubeNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new YouTubeNormalizer();
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
    public function it_can_normalize_youtubes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $youTube = new YouTube('foo', 300, 200);

        return [
            'youtube' => [$youTube, null, true],
            'youtube with format' => [$youTube, 'foo', true],
            'non-youtube' => [$this, null, false],
        ];
    }

    /**
     * @test
     */
    public function it_normalize_youtubes()
    {
        $expected = [
            'type' => 'youtube',
            'id' => 'foo',
            'width' => 300,
            'height' => 200,
        ];

        $this->assertSame($expected, $this->normalizer->normalize(new YouTube('foo', 300, 200)));
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
    public function it_can_denormalize_youtubes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'youtube' => [[], YouTube::class, [], true],
            'block that is a youtube' => [['type' => 'youtube'], Block::class, [], true],
            'block that isn\'t a youtube' => [['type' => 'foo'], Block::class, [], false],
            'non-youtube' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalize_youtubes()
    {
        $json = [
            'type' => 'youtube',
            'id' => 'foo',
            'width' => 300,
            'height' => 200,
        ];

        $this->assertEquals(new YouTube('foo', 300, 200), $this->normalizer->denormalize($json, YouTube::class));
    }
}
