<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\Serializer\AnnualReportNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class AnnualReportNormalizerTest extends TestCase
{
    use NormalizerSamplesTestCase;

    /** @var AnnualReportNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new AnnualReportNormalizer();

        new NormalizerAwareSerializer([$this->normalizer, new FileNormalizer()]);
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
    public function it_can_normalize_annual_reports($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $annualReport = new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', null);

        return [
            'annual report' => [$annualReport, null, true],
            'annual report with format' => [$annualReport, 'foo', true],
            'non-annual report' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_annual_reports(AnnualReport $annualReport, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($annualReport));
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
    public function it_can_denormalize_annual_reports($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'annual report' => [[], AnnualReport::class, [], true],
            'non-annual report' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_annual_reports(AnnualReport $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, AnnualReport::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new AnnualReport(2012, 'http://www.example.com/2012', 'http://www.example.com/2012/assets/annual-report-2012.pdf', 'title', 'impact statement'),
                [
                    'year' => 2012,
                    'uri' => 'http://www.example.com/2012',
                    'title' => 'title',
                    'pdf' => 'http://www.example.com/2012/assets/annual-report-2012.pdf',
                    'impactStatement' => 'impact statement',
                ],
            ],
            'minimum' => [
                new AnnualReport(2012, 'http://www.example.com/2012', null, 'title', null),
                [
                    'year' => 2012,
                    'uri' => 'http://www.example.com/2012',
                    'title' => 'title',
                ],
            ],
        ];
    }

    protected function class() : string
    {
        return AnnualReport::class;
    }

    protected function samples() : string
    {
        return __DIR__.'/../../vendor/elife/api/dist/samples/annual-report/v2';
    }
}
