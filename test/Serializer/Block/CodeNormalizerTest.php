<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Code;
use eLife\ApiSdk\Serializer\Block\CodeNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CodeNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var CodeNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new CodeNormalizer();
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
    public function it_can_normalize_codes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $code = new Code('foo');

        return [
            'code' => [$code, null, true],
            'code with format' => [$code, 'foo', true],
            'non-code' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_codes(Code $code, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($code));
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
    public function it_can_denormalize_codes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'code' => [[], Code::class, [], true],
            'block that is code' => [['type' => 'code'], Block::class, [], true],
            'block that isn\'t code' => [['type' => 'foo'], Block::class, [], false],
            'non-code' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_codes(Code $expected, array $json)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Code::class));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Code('foo', 'PHP'),
                [
                    'type' => 'code',
                    'code' => 'foo',
                    'language' => 'PHP',
                ],
            ],
            'minimum' => [
                new Code('foo'),
                [
                    'type' => 'code',
                    'code' => 'foo',
                ],
            ],
        ];
    }
}
