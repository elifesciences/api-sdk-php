<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class AnnualReports implements Iterator, Collection
{
    use ArrayFromIterator;
    use SlicedIterator;

    private $count;
    private $reports;
    private $descendingOrder = true;
    private $annualReportsClient;
    private $denormalizer;

    public function __construct(AnnualReportsClient $annualReportsClient, DenormalizerInterface $denormalizer)
    {
        $this->reports = new ArrayObject();
        $this->annualReportsClient = $annualReportsClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(int $year) : PromiseInterface
    {
        if (isset($this->reports[$year])) {
            return $this->reports[$year];
        }

        return $this->reports[$year] = $this->annualReportsClient
            ->getReport(
                ['Accept' => new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT, 1)],
                $year
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), AnnualReport::class);
            });
    }

    public function slice(int $offset, int $length = null) : Collection
    {
        if (null === $length) {
            return new PromiseCollection($this->all()
                ->then(function (Collection $collection) use ($offset) {
                    return $collection->slice($offset);
                })
            );
        }

        return new PromiseCollection($this->annualReportsClient
            ->listReports(
                ['Accept' => new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $reports = [];

                foreach ($result['items'] as $report) {
                    if (isset($this->reports[$report['year']])) {
                        $reports[] = $this->reports[$report['year']]->wait();
                    } else {
                        $reports[] = $report = $this->denormalizer->denormalize($report, AnnualReport::class);
                        $this->reports[$report->getYear()] = promise_for($report);
                    }
                }

                return new ArrayCollection($reports);
            })
        );
    }

    public function reverse() : Collection
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }

    public function count() : int
    {
        if (null === $this->count) {
            $this->slice(0, 1)->count();
        }

        return $this->count;
    }
}
