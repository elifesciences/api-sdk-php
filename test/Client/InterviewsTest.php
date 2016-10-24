<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Interviews;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Interview;
use test\eLife\ApiSdk\ApiTestCase;

final class InterviewsTest extends ApiTestCase
{
    /** @var Interviews */
    private $interviews;

    /**
     * @before
     */
    protected function setUpInterviews()
    {
        $this->interviews = (new ApiSdk($this->getHttpClient()))->interviews();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->interviews);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockInterviewListCall(1, 1, 200);
        $this->mockInterviewListCall(1, 100, 200);
        $this->mockInterviewListCall(2, 100, 200);

        foreach ($this->interviews as $i => $interview) {
            $this->assertInstanceOf(Interview::class, $interview);
            $this->assertSame('interview'.$i, $interview->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockInterviewListCall(1, 1, 10);

        $this->assertSame(10, $this->interviews->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockInterviewListCall(1, 1, 10);
        $this->mockInterviewListCall(1, 100, 10);

        $array = $this->interviews->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $interview) {
            $this->assertInstanceOf(Interview::class, $interview);
            $this->assertSame('interview'.($i + 1), $interview->getId());
        }
    }

    /**
     * @test
     */
    public function it_gets_an_interview()
    {
        $this->mockInterviewCall(7);

        $interview = $this->interviews->get('interview7')->wait();

        $this->assertInstanceOf(Interview::class, $interview);
        $this->assertSame('interview7', $interview->getId());

        $this->assertInstanceOf(Paragraph::class, $interview->getContent()->toArray()[0]);
        $this->assertSame('Interview 7 text', $interview->getContent()->toArray()[0]->getText());
    }

    /**
     * @test
     */
    public function it_reuses_already_known_interviews()
    {
        $this->mockInterviewListCall(1, 1, 1);
        $this->mockInterviewListCall(1, 100, 1);

        $this->interviews->toArray();

        $interview = $this->interviews->get('interview1')->wait();

        $this->assertInstanceOf(Interview::class, $interview);
        $this->assertSame('interview1', $interview->getId());

        $this->mockInterviewCall(1);

        $this->assertInstanceOf(Paragraph::class, $interview->getContent()->toArray()[0]);
        $this->assertSame('Interview 1 text', $interview->getContent()->toArray()[0]->getText());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockInterviewListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->interviews->slice($offset, $length) as $i => $interview) {
            $this->assertInstanceOf(Interview::class, $interview);
            $this->assertSame('interview'.($expected[$i]), $interview->getId());
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
        $this->mockInterviewListCall(1, 1, 3);
        $this->mockInterviewListCall(1, 100, 3);

        $map = function (Interview $interview) {
            return $interview->getId();
        };

        $this->assertSame(['interview1', 'interview2', 'interview3'], $this->interviews->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockInterviewListCall(1, 1, 5);
        $this->mockInterviewListCall(1, 100, 5);

        $filter = function (Interview $interview) {
            return substr($interview->getId(), -1) > 3;
        };

        foreach ($this->interviews->filter($filter) as $i => $interview) {
            $this->assertSame('interview'.($i + 4), $interview->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockInterviewListCall(1, 1, 5);
        $this->mockInterviewListCall(1, 100, 5);

        $reduce = function (int $carry = null, Interview $interview) {
            return $carry + substr($interview->getId(), -1);
        };

        $this->assertSame(115, $this->interviews->reduce($reduce, 100)->wait());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockInterviewListCall(1, 1, 5);
        $this->mockInterviewListCall(1, 100, 5);

        $sort = function (Interview $a, Interview $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->interviews->sort($sort) as $i => $interview) {
            $this->assertSame('interview'.(5 - $i), $interview->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockInterviewListCall(1, 1, 5, false);
        $this->mockInterviewListCall(1, 100, 5, false);

        foreach ($this->interviews->reverse() as $i => $interview) {
            $this->assertSame('interview'.$i, $interview->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockInterviewListCall(1, 1, 10);

        $this->interviews->count();

        $this->assertSame(10, $this->interviews->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockInterviewListCall(1, 1, 200);
        $this->mockInterviewListCall(1, 100, 200);
        $this->mockInterviewListCall(2, 100, 200);

        $this->interviews->toArray();

        $this->mockInterviewListCall(1, 1, 200, false);
        $this->mockInterviewListCall(1, 100, 200, false);
        $this->mockInterviewListCall(2, 100, 200, false);

        $this->interviews->reverse()->toArray();
    }
}
