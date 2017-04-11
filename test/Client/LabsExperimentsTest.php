<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\LabsExperiments;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\LabsExperiment;
use test\eLife\ApiSdk\ApiTestCase;

final class LabsExperimentsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var LabsExperiments */
    private $labsExperiments;

    /**
     * @before
     */
    protected function setUpLabsExperiments()
    {
        $this->labsExperiments = (new ApiSdk($this->getHttpClient()))->labsExperiments();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->labsExperiments);
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

        $this->assertFalse($this->labsExperiments->isEmpty());
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
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockLabsExperimentListCall(1, 1, 1);

        $this->assertTrue(isset($this->labsExperiments[0]));
        $this->assertSame(1, $this->labsExperiments[0]->getNumber());

        $this->mockNotFound(
            'labs-experiments?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT_LIST, 1)]
        );

        $this->assertFalse(isset($this->labsExperiments[5]));
        $this->assertSame(null, $this->labsExperiments[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->labsExperiments[0] = 'foo';
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

        $this->assertInstanceOf(Paragraph::class, $labsExperiment->getContent()[0]);
        $this->assertSame('Labs experiment 7 text', $labsExperiment->getContent()[0]->getText());
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockLabsExperimentListCall(1, 1, 5);
        $this->mockLabsExperimentListCall(1, 100, 5);

        $values = $this->labsExperiments->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'Labs experiment 1 title', 'Labs experiment 2 title', 'Labs experiment 3 title', 'Labs experiment 4 title', 'Labs experiment 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockLabsExperimentListCall(1, 1, 5);
        $this->mockLabsExperimentListCall(1, 100, 5);

        $values = $this->labsExperiments->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['Labs experiment 1 title', 'Labs experiment 2 title', 'Labs experiment 3 title', 'Labs experiment 4 title', 'Labs experiment 5 title', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockLabsExperimentListCall(1, 1, 5);
        $this->mockLabsExperimentListCall(1, 100, 5);

        $values = $this->labsExperiments->drop(2)->map($this->tidyValue());

        $this->assertSame(['Labs experiment 1 title', 'Labs experiment 2 title', 'Labs experiment 4 title', 'Labs experiment 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockLabsExperimentListCall(1, 1, 5);
        $this->mockLabsExperimentListCall(1, 100, 5);

        $values = $this->labsExperiments->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['Labs experiment 1 title', 'Labs experiment 2 title', 2, 'Labs experiment 3 title', 'Labs experiment 4 title', 'Labs experiment 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockLabsExperimentListCall(1, 1, 5);
        $this->mockLabsExperimentListCall(1, 100, 5);

        $values = $this->labsExperiments->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['Labs experiment 1 title', 'Labs experiment 2 title', 2, 'Labs experiment 4 title', 'Labs experiment 5 title'], $values->toArray());
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

        $this->assertSame(115, $this->labsExperiments->reduce($reduce, 100));
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
