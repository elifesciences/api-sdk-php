<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\GoogleMap;
use eLife\ApiSdk\Serializer\Block\GoogleMapNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class GoogleMapNormalizerTest extends TestCase
{
    /** @var GoogleMapNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new GoogleMapNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
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
    public function it_can_normalize_google_maps($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $googleMap = new GoogleMap('foo', 'title');

        return [
            'google-map' => [$googleMap, null, true],
            'google-map with format' => [$googleMap, 'foo', true],
            'non-google-map' => [$this, null, false],
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
    public function it_can_denormalize_google_maps($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'google-map' => [[], GoogleMap::class, [], true],
            'block that is a google-map' => [['type' => 'google-map'], Block::class, [], true],
            'block that isn\'t a google-map' => [['type' => 'foo'], Block::class, [], false],
            'non-google-map' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalizes_google_maps()
    {
        $json = [
            'type' => 'google-map',
            'id' => 'foo',
            'title' => 'title',
        ];
        $expected = new GoogleMap('foo', 'title');

        $this->assertEquals($expected, $this->normalizer->denormalize($json, GoogleMap::class));
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, GoogleMap::class));
    }
}
