<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Bioprotocol;
use eLife\ApiSdk\Serializer\BioprotocolNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class BioprotocolNormalizerTest extends TestCase
{
    use NormalizerSamplesTestCase;

    /** @var BioprotocolNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new BioprotocolNormalizer();
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_bioprotocols($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $bioprotocol = Builder::for(Bioprotocol::class)->__invoke();

        return [
            'bioprotocol' => [$bioprotocol, null, true],
            'bioprotocol with format' => [$bioprotocol, 'foo', true],
            'non-bioprotocol' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_bioprotocols(Bioprotocol $bioprotocol, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($bioprotocol));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_bioprotocols($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'bioprotocol' => [[], Bioprotocol::class, [], true],
            'non-bioprotocol' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_bioprotocols(Bioprotocol $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Bioprotocol::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                Builder::for(Bioprotocol::class)
                    ->withSectionId('section id')
                    ->withTitle('title')
                    ->withStatus(true)
                    ->withUri('https://example.com')
                    ->__invoke(),
                [
                    'sectionId' => 'section id',
                    'title' => 'title',
                    'status' => true,
                    'uri' => 'https://example.com',
                ],
            ],
        ];
    }

    protected function class() : string
    {
        return Bioprotocol::class;
    }

    protected static function samples(): \Generator
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/bioprotocol/v1/*.json#items';
    }
}
