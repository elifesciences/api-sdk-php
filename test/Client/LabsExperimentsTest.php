<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiSdk\Client\LabsExperiments;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\LabsExperimentNormalizer;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\ApiTestCase;

final class LabsExperimentsTest extends ApiTestCase
{
    /** @var LabsExperiments */
    private $labsExperiments;

    /**
     * @before
     */
    protected function setUpLabsExperiments()
    {
        $serializer = new Serializer([
            new LabsExperimentNormalizer(),
            new ImageNormalizer(),
            new Block\ParagraphNormalizer(),
        ]);
        $this->labsExperiments = new LabsExperiments(
            new LabsClient($this->getHttpClient()),
            $serializer
        );
    }

    /**
     * @test
     */
    public function it_is_a_collection()
    {
        $this->assertInstanceOf(Collection::class, $this->labsExperiments);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockLabsExperimentListCall(1, 1, 200);
        $this->mockLabsExperimentListCall(1, 100, 200);
        $this->mockLabsExperimentListCall(2, 100, 200);

        foreach ($this->labsExperiments as $i => $labsExperiment) {
            $this->assertInstanceOf(LabsExperiment::class, $labsExperiment);
            $this->assertSame($i, $labsExperiment->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockLabsExperimentListCall(1, 1, 10);

        $this->assertSame(10, $this->labsExperiments->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockLabsExperimentListCall(1, 1, 10);
        $this->mockLabsExperimentListCall(1, 100, 10);

        $array = $this->labsExperiments->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $labsExperiment) {
            $this->assertInstanceOf(LabsExperiment::class, $labsExperiment);
            $this->assertSame($i + 1, $labsExperiment->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_gets_a_labs_experiment()
    {
        $this->mockLabsExperimentCall(7);

        $labsExperiment = $this->labsExperiments->get(7)->wait();

        $this->assertInstanceOf(LabsExperiment::class, $labsExperiment);
        $this->assertSame(7, $labsExperiment->getNumber());

        $this->assertInstanceOf(Paragraph::class, $labsExperiment->getContent()->toArray()[0]);
        $this->assertSame('Labs experiment 7 text', $labsExperiment->getContent()->toArray()[0]->getText());
    }

    /**
     * @test
     */
    public function it_reuses_already_known_labs_experiments()
    {
        $this->mockLabsExperimentListCall(1, 1, 10);
        $this->mockLabsExperimentListCall(1, 100, 10);

        $this->labsExperiments->toArray();

        $labsExperiment = $this->labsExperiments->get(7)->wait();

        $this->assertInstanceOf(LabsExperiment::class, $labsExperiment);
        $this->assertSame(7, $labsExperiment->getNumber());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockLabsExperimentListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->labsExperiments->slice($offset, $length) as $i => $labsExperiment) {
            $this->assertInstanceOf(LabsExperiment::class, $labsExperiment);
            $this->assertSame($expected[$i], $labsExperiment->getNumber());
        }
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [
                1,
                1,
                [2],
                [
                    ['page' => 2, 'per-page' => 1],
                ],
            ],
            'offset -2, no length' => [
                -2,
                null,
                [4, 5],
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
        $this->mockLabsExperimentListCall(1, 1, 3);
        $this->mockLabsExperimentListCall(1, 100, 3);

        $map = function (LabsExperiment $labsExperiment) {
            return $labsExperiment->getNumber();
        };

        $this->assertSame([1, 2, 3], $this->labsExperiments->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockLabsExperimentListCall(1, 1, 5);
        $this->mockLabsExperimentListCall(1, 100, 5);

        $filter = function (LabsExperiment $labsExperiment) {
            return substr($labsExperiment->getNumber(), -1) > 3;
        };

        foreach ($this->labsExperiments->filter($filter) as $i => $labsExperiment) {
            $this->assertSame($i + 4, $labsExperiment->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockLabsExperimentListCall(1, 1, 5);
        $this->mockLabsExperimentListCall(1, 100, 5);

        $reduce = function (int $carry = null, LabsExperiment $labsExperiment) {
            return $carry + $labsExperiment->getNumber();
        };

        $this->assertSame(115, $this->labsExperiments->reduce($reduce, 100)->wait());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockLabsExperimentListCall(1, 1, 5);
        $this->mockLabsExperimentListCall(1, 100, 5);

        $sort = function (LabsExperiment $a, LabsExperiment $b) {
            return substr($b->getNumber(), -1) <=> substr($a->getNumber(), -1);
        };

        foreach ($this->labsExperiments->sort($sort) as $i => $labsExperiment) {
            $this->assertSame(5 - $i, $labsExperiment->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockLabsExperimentListCall(1, 1, 5, false);
        $this->mockLabsExperimentListCall(1, 100, 5, false);

        foreach ($this->labsExperiments->reverse() as $i => $labsExperiment) {
            $this->assertSame($i, $labsExperiment->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockLabsExperimentListCall(1, 1, 10);

        $this->labsExperiments->count();

        $this->assertSame(10, $this->labsExperiments->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockLabsExperimentListCall(1, 1, 200);
        $this->mockLabsExperimentListCall(1, 100, 200);
        $this->mockLabsExperimentListCall(2, 100, 200);

        $this->labsExperiments->toArray();

        $this->mockLabsExperimentListCall(1, 1, 200, false);
        $this->mockLabsExperimentListCall(1, 100, 200, false);
        $this->mockLabsExperimentListCall(2, 100, 200, false);

        $this->labsExperiments->reverse()->toArray();
    }
}