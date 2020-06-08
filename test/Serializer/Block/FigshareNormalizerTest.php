<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figshare;
use eLife\ApiSdk\Serializer\Block\FigshareNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class FigshareNormalizerTest extends TestCase
{
    /** @var FigshareNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new FigshareNormalizer();

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
    public function it_can_normalize_figshares($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        return [
            'figshare' => [$figshare, null, true],
            'figshare with format' => [$figshare, 'foo', true],
            'non-figshare' => [$this, null, false],
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
    public function it_can_denormalize_figshares($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'figshare' => [[], Figshare::class, [], true],
            'block that is a figshare' => [['type' => 'figshare'], Block::class, [], true],
            'block that isn\'t a figshare' => [['type' => 'foo'], Block::class, [], false],
            'non-figshare' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalizes_figshares()
    {
        $json = [
            'type' => 'figshare',
            'id' => 'foo',
            'title' => 'title',
            'width' => '300',
            'height' => '200',
        ];
        $expected = new Figshare('foo', 'title', 300, 200);

        $this->assertEquals($expected, $this->normalizer->denormalize($json, Figshare::class));
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Figshare::class));
    }
}
