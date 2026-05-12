<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Code;
use eLife\ApiSdk\Serializer\Block\CodeNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PHPUnit\Framework\Attributes\Before as Before;

final class CodeNormalizerTest extends TestCase
{
    /** @var CodeNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new CodeNormalizer();
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_codes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $code = new Code('foo');

        return [
            'code' => [$code, null, true],
            'code with format' => [$code, 'foo', true],
            'non-code' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_codes(Code $code, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($code));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_codes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'code' => [[], Code::class, [], true],
            'block that is code' => [['type' => 'code'], Block::class, [], true],
            'block that isn\'t code' => [['type' => 'foo'], Block::class, [], false],
            'non-code' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_codes(Code $expected, array $json)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Code::class));
    }

    public static function normalizeProvider() : array
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
