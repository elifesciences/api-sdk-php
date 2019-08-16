<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Bioprotocol;
use eLife\ApiSdk\Serializer\BioprotocolNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class BioprotocolNormalizerTest extends TestCase
{
    /** @var BioprotocolNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new BioprotocolNormalizer();
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
    public function it_can_normalize_bioprotocols($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $bioprotocol = Builder::for(Bioprotocol::class)->__invoke();

        return [
            'bioprotocol' => [$bioprotocol, null, true],
            'bioprotocol with format' => [$bioprotocol, 'foo', true],
            'non-bioprotocol' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_bioprotocols(Bioprotocol $bioprotocol, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($bioprotocol));
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
    public function it_can_denormalize_bioprotocols($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'bioprotocol' => [[], Bioprotocol::class, [], true],
            'non-bioprotocol' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_bioprotocols(Bioprotocol $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Bioprotocol::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                Builder::for(Bioprotocol::class)
                    ->withSectionId('section id')
                    ->withStatus(true)
                    ->withUri('https://example.com')
                    ->__invoke(),
                [
                    'sectionId' => 'section id',
                    'status' => true,
                    'uri' => 'https://example.com',
                ],
            ],
        ];
    }
}
