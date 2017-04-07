<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\Client\AnnualReports;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\Serializer\AnnualReportNormalizer;
use eLife\ApiSdk\Serializer\AssetFileNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use test\eLife\ApiSdk\ApiTestCase;

final class AnnualReportsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var AnnualReports */
    private $annualReports;

    /**
     * @before
     */
    protected function setUpAnnualReports()
    {
        $this->annualReports = new AnnualReports(
            new AnnualReportsClient($this->getHttpClient()),
            new NormalizerAwareSerializer([new AnnualReportNormalizer(), new ImageNormalizer(), new AssetFileNormalizer(), new FileNormalizer()])
        );
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->annualReports);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockAnnualReportListCall(1, 1, 200);
        $this->mockAnnualReportListCall(1, 100, 200);
        $this->mockAnnualReportListCall(2, 100, 200);

        foreach ($this->annualReports as $i => $annualReport) {
            $this->assertInstanceOf(AnnualReport::class, $annualReport);
            $this->assertSame(2011 + $i, $annualReport->getYear());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockAnnualReportListCall(1, 1, 10);

        $this->assertFalse($this->annualReports->isEmpty());
        $this->assertSame(10, $this->annualReports->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockAnnualReportListCall(1, 1, 10);
        $this->mockAnnualReportListCall(1, 100, 10);

        $array = $this->annualReports->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $annualReport) {
            $this->assertInstanceOf(AnnualReport::class, $annualReport);
            $this->assertSame(2011 + $i + 1, $annualReport->getYear());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockAnnualReportListCall(1, 1, 1);

        $this->assertTrue(isset($this->annualReports[0]));
        $this->assertSame(2012, $this->annualReports[0]->getYear());

        $this->mockNotFound(
            'annual-reports?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT_LIST, 1)]
        );

        $this->assertFalse(isset($this->annualReports[5]));
        $this->assertSame(null, $this->annualReports[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->annualReports[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_an_annual_report()
    {
        $this->mockAnnualReportCall(2012);

        $annualReport = $this->annualReports->get(2012)->wait();

        $this->assertInstanceOf(AnnualReport::class, $annualReport);
        $this->assertSame(2012, $annualReport->getYear());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockAnnualReportListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->annualReports->slice($offset, $length) as $i => $annualReport) {
            $this->assertInstanceOf(AnnualReport::class, $annualReport);
            $this->assertSame($expected[$i], $annualReport->getYear());
        }
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [
                1,
                1,
                [2013],
                [
                    ['page' => 2, 'per-page' => 1],
                ],
            ],
            'offset -2, no length' => [
                -2,
                null,
                [2015, 2016],
                [
                    ['page' => 1, 'per-page' => 1],
                    ['page' => 1, 'per-page' => 100],
                ],
            ],
            'offset 6, no length' => [
                6,
                null,
                [],
                [
                    ['page' => 1, 'per-page' => 1],
                    ['page' => 1, 'per-page' => 100],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockAnnualReportListCall(1, 1, 3);
        $this->mockAnnualReportListCall(1, 100, 3);

        $map = function (AnnualReport $annualReport) {
            return $annualReport->getYear();
        };

        $this->assertSame([2012, 2013, 2014], $this->annualReports->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockAnnualReportListCall(1, 1, 5);
        $this->mockAnnualReportListCall(1, 100, 5);

        $filter = function (AnnualReport $annualReport) {
            return $annualReport->getYear() > 2014;
        };

        foreach ($this->annualReports->filter($filter) as $i => $annualReport) {
            $this->assertSame(2011 + $i + 4, $annualReport->getYear());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockAnnualReportListCall(1, 1, 5);
        $this->mockAnnualReportListCall(1, 100, 5);

        $reduce = function (int $carry = null, AnnualReport $annualReport) {
            return $carry + $annualReport->getYear();
        };

        $this->assertSame(10170, $this->annualReports->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockAnnualReportListCall(1, 1, 5);
        $this->mockAnnualReportListCall(1, 100, 5);

        $sort = function (AnnualReport $a, AnnualReport $b) {
            return $b->getYear() <=> $a->getYear();
        };

        foreach ($this->annualReports->sort($sort) as $i => $annualReport) {
            $this->assertSame(2011 + (5 - $i), $annualReport->getYear());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockAnnualReportListCall(1, 1, 5, false);
        $this->mockAnnualReportListCall(1, 100, 5, false);

        foreach ($this->annualReports->reverse() as $i => $annualReport) {
            $this->assertSame(2011 + $i, $annualReport->getYear());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockAnnualReportListCall(1, 1, 10);

        $this->annualReports->count();

        $this->assertSame(10, $this->annualReports->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockAnnualReportListCall(1, 1, 200);
        $this->mockAnnualReportListCall(1, 100, 200);
        $this->mockAnnualReportListCall(2, 100, 200);

        $this->annualReports->toArray();

        $this->mockAnnualReportListCall(1, 1, 200, false);
        $this->mockAnnualReportListCall(1, 100, 200, false);
        $this->mockAnnualReportListCall(2, 100, 200, false);

        $this->annualReports->reverse()->toArray();
    }
}
