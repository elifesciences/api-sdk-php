<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\AnnualReport;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class AnnualReports implements Iterator, Sequence
{
    use Client;

    const VERSION_ANNUAL_REPORT = 2;
    const VERSION_ANNUAL_REPORT_LIST = 2;

    private $descendingOrder = true;
    private $annualReportsClient;
    private $denormalizer;

    public function __construct(AnnualReportsClient $annualReportsClient, DenormalizerInterface $denormalizer)
    {
        $this->annualReportsClient = $annualReportsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(int $year) : PromiseInterface
    {
        return $this->annualReportsClient
            ->getReport(
                ['Accept' => (string) new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT, self::VERSION_ANNUAL_REPORT)],
                $year
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), AnnualReport::class);
            });
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        if (null === $length) {
            return new PromiseSequence($this->all()
                ->then(function (Sequence $sequence) use ($offset) {
                    return $sequence->slice($offset);
                })
            );
        }

        return new PromiseSequence($this->annualReportsClient
            ->listReports(
                ['Accept' => (string) new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT_LIST, self::VERSION_ANNUAL_REPORT_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $report) {
                    return $this->denormalizer->denormalize($report, AnnualReport::class);
                }, $result['items']);
            })
        );
    }

    public function reverse() : Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }
}
