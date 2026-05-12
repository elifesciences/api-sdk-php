<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\GoogleMap;
use eLife\ApiSdk\Serializer\Block\GoogleMapNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class GoogleMapNormalizerTest extends TestCase
{
    /** @var GoogleMapNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new GoogleMapNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
        ]);
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_google_maps($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $googleMap = new GoogleMap('foo', 'title');

        return [
            'google-map' => [$googleMap, null, true],
            'google-map with format' => [$googleMap, 'foo', true],
            'non-google-map' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_google_maps($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'google-map' => [[], GoogleMap::class, [], true],
            'block that is a google-map' => [['type' => 'google-map'], Block::class, [], true],
            'block that isn\'t a google-map' => [['type' => 'foo'], Block::class, [], false],
            'non-google-map' => [[], self::class, [], false],
        ];
    }

    #[Test]
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
