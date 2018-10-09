<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\JobAdvertsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\JobAdverts;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\JobAdvert;
use test\eLife\ApiSdk\ApiTestCase;

final class JobAdvertsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var JobAdverts */
    private $jobAdverts;

    /**
     * @before
     */
    protected function setUpJobAdverts()
    {
        $this->jobAdverts = (new ApiSdk($this->getHttpClient()))->jobAdverts();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->jobAdverts);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockJobAdvertListCall(1, 1, 200);
        $this->mockJobAdvertListCall(1, 100, 200);
        $this->mockJobAdvertListCall(2, 100, 200);

        foreach ($this->jobAdverts as $i => $jobAdvert) {
            $this->assertInstanceOf(JobAdvert::class, $jobAdvert);
            $this->assertSame('job-advert'.$i, $jobAdvert->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockJobAdvertListCall(1, 1, 10);

        $this->assertFalse($this->jobAdverts->isEmpty());
        $this->assertSame(10, $this->jobAdverts->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockJobAdvertListCall(1, 1, 10);
        $this->mockJobAdvertListCall(1, 100, 10);

        $array = $this->jobAdverts->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $jobAdvert) {
            $this->assertInstanceOf(JobAdvert::class, $jobAdvert);
            $this->assertSame('job-advert'.($i + 1), $jobAdvert->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockJobAdvertListCall(1, 1, 1);

        $this->assertTrue(isset($this->jobAdverts[0]));
        // TODO: change job-advert to jobAdvert
        $this->assertSame('job-advert1', $this->jobAdverts[0]->getId());

        $this->mockNotFound(
            'job-adverts?page=6&per-page=1&show=all&order=desc',
            ['Accept' => new MediaType(JobAdvertsClient::TYPE_JOB_ADVERT_LIST, 1)]
        );

        $this->assertFalse(isset($this->jobAdverts[5]));
        $this->assertSame(null, $this->jobAdverts[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->jobAdverts[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_job_advert()
    {
        $this->mockJobAdvertCall('job-advert7');

        $jobAdvert = $this->jobAdverts->get('job-advert7')->wait();

        $this->assertInstanceOf(JobAdvert::class, $jobAdvert);
        $this->assertSame('job-advert7', $jobAdvert->getId());

        $this->assertInstanceOf(Paragraph::class, $jobAdvert->getContent()[0]);
        $this->assertSame('Job advert job-advert7 text', $jobAdvert->getContent()[0]->getText());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_open_and_closed()
    {
        $this->mockJobAdvertListCall(1, 1, 5, true, 'open');
        $this->mockJobAdvertListCall(1, 100, 5, true, 'open');

        foreach ($this->jobAdverts->show('open') as $i => $jobAdvert) {
            $this->assertSame('job-advert'.$i, $jobAdvert->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_open_and_closed()
    {
        $this->mockJobAdvertListCall(1, 1, 10);

        $this->jobAdverts->count();

        $this->mockJobAdvertListCall(1, 1, 10, true, 'open');

        $this->assertSame(10, $this->jobAdverts->show('open')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_open_and_closed()
    {
        $this->mockJobAdvertListCall(1, 1, 200);
        $this->mockJobAdvertListCall(1, 100, 200);
        $this->mockJobAdvertListCall(2, 100, 200);

        $this->jobAdverts->toArray();

        $this->mockJobAdvertListCall(1, 1, 200, true, 'open');
        $this->mockJobAdvertListCall(1, 100, 200, true, 'open');
        $this->mockJobAdvertListCall(2, 100, 200, true, 'open');

        $this->jobAdverts->show('open')->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockJobAdvertListCall(1, 1, 5);
        $this->mockJobAdvertListCall(1, 100, 5);

        $values = $this->jobAdverts->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'job-advert1', 'job-advert2', 'job-advert3', 'job-advert4', 'job-advert5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockJobAdvertListCall(1, 1, 5);
        $this->mockJobAdvertListCall(1, 100, 5);

        $values = $this->jobAdverts->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['job-advert1', 'job-advert2', 'job-advert3', 'job-advert4', 'job-advert5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockJobAdvertListCall(1, 1, 5);
        $this->mockJobAdvertListCall(1, 100, 5);

        $values = $this->jobAdverts->drop(2)->map($this->tidyValue());

        $this->assertSame(['job-advert1', 'job-advert2', 'job-advert4', 'job-advert5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockJobAdvertListCall(1, 1, 5);
        $this->mockJobAdvertListCall(1, 100, 5);

        $values = $this->jobAdverts->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['job-advert1', 'job-advert2', 2, 'job-advert3', 'job-advert4', 'job-advert5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockJobAdvertListCall(1, 1, 5);
        $this->mockJobAdvertListCall(1, 100, 5);

        $values = $this->jobAdverts->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['job-advert1', 'job-advert2', 2, 'job-advert4', 'job-advert5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockJobAdvertListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->jobAdverts->slice($offset, $length) as $i => $jobAdvert) {
            $this->assertInstanceOf(JobAdvert::class, $jobAdvert);
            $this->assertSame('job-advert'.($expected[$i]), $jobAdvert->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockJobAdvertListCall(1, 1, 3);
        $this->mockJobAdvertListCall(1, 100, 3);

        $map = function (JobAdvert $jobAdvert) {
            return $jobAdvert->getId();
        };

        $this->assertSame(['job-advert1', 'job-advert2', 'job-advert3'], $this->jobAdverts->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockJobAdvertListCall(1, 1, 5);
        $this->mockJobAdvertListCall(1, 100, 5);

        $filter = function (JobAdvert $jobAdvert) {
            return substr($jobAdvert->getId(), -1) > 3;
        };

        foreach ($this->jobAdverts->filter($filter) as $i => $jobAdvert) {
            $this->assertSame('job-advert'.($i + 4), $jobAdvert->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockJobAdvertListCall(1, 1, 5);
        $this->mockJobAdvertListCall(1, 100, 5);

        $reduce = function (int $carry = null, JobAdvert $jobAdvert) {
            return $carry + substr($jobAdvert->getId(), -1);
        };

        $this->assertSame(115, $this->jobAdverts->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->jobAdverts, $this->jobAdverts->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockJobAdvertListCall(1, 1, 5);
        $this->mockJobAdvertListCall(1, 100, 5);

        $sort = function (JobAdvert $a, JobAdvert $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->jobAdverts->sort($sort) as $i => $jobAdvert) {
            $this->assertSame('job-advert'.(5 - $i), $jobAdvert->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockJobAdvertListCall(1, 1, 5, false);
        $this->mockJobAdvertListCall(1, 100, 5, false);

        foreach ($this->jobAdverts->reverse() as $i => $jobAdvert) {
            $this->assertSame('job-advert'.$i, $jobAdvert->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockJobAdvertListCall(1, 1, 10);

        $this->jobAdverts->count();

        $this->assertSame(10, $this->jobAdverts->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockJobAdvertListCall(1, 1, 200);
        $this->mockJobAdvertListCall(1, 100, 200);
        $this->mockJobAdvertListCall(2, 100, 200);

        $this->jobAdverts->toArray();

        $this->mockJobAdvertListCall(1, 1, 200, false);
        $this->mockJobAdvertListCall(1, 100, 200, false);
        $this->mockJobAdvertListCall(2, 100, 200, false);

        $this->jobAdverts->reverse()->toArray();
    }
}
