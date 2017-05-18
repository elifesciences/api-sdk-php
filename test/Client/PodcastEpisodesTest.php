<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class PodcastEpisodesTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var PodcastEpisodes */
    private $podcastEpisodes;

    /**
     * @before
     */
    protected function setUpPodcastEpisodes()
    {
        $this->podcastEpisodes = (new ApiSdk($this->getHttpClient()))->podcastEpisodes();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->podcastEpisodes);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 200);
        $this->mockPodcastEpisodeListCall(1, 100, 200);
        $this->mockPodcastEpisodeListCall(2, 100, 200);

        foreach ($this->podcastEpisodes as $i => $podcastEpisode) {
            $this->assertInstanceOf(PodcastEpisode::class, $podcastEpisode);
            $this->assertSame($i, $podcastEpisode->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 10);

        $this->assertFalse($this->podcastEpisodes->isEmpty());
        $this->assertSame(10, $this->podcastEpisodes->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 10);
        $this->mockPodcastEpisodeListCall(1, 100, 10);

        $array = $this->podcastEpisodes->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $podcastEpisode) {
            $this->assertInstanceOf(PodcastEpisode::class, $podcastEpisode);
            $this->assertSame($i + 1, $podcastEpisode->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 1);

        $this->assertTrue(isset($this->podcastEpisodes[0]));
        $this->assertSame(1, $this->podcastEpisodes[0]->getNumber());

        $this->mockNotFound(
            'podcast-episodes?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)]
        );

        $this->assertFalse(isset($this->podcastEpisodes[5]));
        $this->assertSame(null, $this->podcastEpisodes[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->podcastEpisodes[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_podcast_episode()
    {
        $this->mockPodcastEpisodeCall(7, true);

        $podcastEpisode = $this->podcastEpisodes->get(7)->wait();

        $this->assertInstanceOf(PodcastEpisode::class, $podcastEpisode);
        $this->assertSame(7, $podcastEpisode->getNumber());

        $this->assertInstanceOf(PodcastEpisodeChapter::class, $podcastEpisode->getChapters()[0]);
        $this->assertSame('Chapter title', $podcastEpisode->getChapters()[0]->getTitle());
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5);
        $this->mockPodcastEpisodeListCall(1, 100, 5);

        $values = $this->podcastEpisodes->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'Podcast episode 1 title', 'Podcast episode 2 title', 'Podcast episode 3 title', 'Podcast episode 4 title', 'Podcast episode 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5);
        $this->mockPodcastEpisodeListCall(1, 100, 5);

        $values = $this->podcastEpisodes->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['Podcast episode 1 title', 'Podcast episode 2 title', 'Podcast episode 3 title', 'Podcast episode 4 title', 'Podcast episode 5 title', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5);
        $this->mockPodcastEpisodeListCall(1, 100, 5);

        $values = $this->podcastEpisodes->drop(2)->map($this->tidyValue());

        $this->assertSame(['Podcast episode 1 title', 'Podcast episode 2 title', 'Podcast episode 4 title', 'Podcast episode 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5);
        $this->mockPodcastEpisodeListCall(1, 100, 5);

        $values = $this->podcastEpisodes->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['Podcast episode 1 title', 'Podcast episode 2 title', 2, 'Podcast episode 3 title', 'Podcast episode 4 title', 'Podcast episode 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5);
        $this->mockPodcastEpisodeListCall(1, 100, 5);

        $values = $this->podcastEpisodes->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['Podcast episode 1 title', 'Podcast episode 2 title', 2, 'Podcast episode 4 title', 'Podcast episode 5 title'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockPodcastEpisodeListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->podcastEpisodes->slice($offset, $length) as $i => $podcastEpisode) {
            $this->assertInstanceOf(PodcastEpisode::class, $podcastEpisode);
            $this->assertSame($expected[$i], $podcastEpisode->getNumber());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 3);
        $this->mockPodcastEpisodeListCall(1, 100, 3);

        $map = function (PodcastEpisode $podcastEpisode) {
            return $podcastEpisode->getNumber();
        };

        $this->assertSame([1, 2, 3],
            $this->podcastEpisodes->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5);
        $this->mockPodcastEpisodeListCall(1, 100, 5);

        $filter = function (PodcastEpisode $podcastEpisode) {
            return $podcastEpisode->getNumber() > 3;
        };

        foreach ($this->podcastEpisodes->filter($filter) as $i => $podcastEpisode) {
            $this->assertSame($i + 4, $podcastEpisode->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5);
        $this->mockPodcastEpisodeListCall(1, 100, 5);

        $reduce = function (int $carry = null, PodcastEpisode $podcastEpisode) {
            return $carry + $podcastEpisode->getNumber();
        };

        $this->assertSame(115, $this->podcastEpisodes->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->podcastEpisodes, $this->podcastEpisodes->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5);
        $this->mockPodcastEpisodeListCall(1, 100, 5);

        $sort = function (PodcastEpisode $a, PodcastEpisode $b) {
            return $b->getNumber() <=> $a->getNumber();
        };

        foreach ($this->podcastEpisodes->sort($sort) as $i => $podcastEpisode) {
            $this->assertSame(5 - $i, $podcastEpisode->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5, false);
        $this->mockPodcastEpisodeListCall(1, 100, 5, false);

        foreach ($this->podcastEpisodes->reverse() as $i => $podcastEpisode) {
            $this->assertSame($i, $podcastEpisode->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 10);

        $this->podcastEpisodes->count();

        $this->assertSame(10, $this->podcastEpisodes->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 200);
        $this->mockPodcastEpisodeListCall(1, 100, 200);
        $this->mockPodcastEpisodeListCall(2, 100, 200);

        $this->podcastEpisodes->toArray();

        $this->mockPodcastEpisodeListCall(1, 1, 200, false);
        $this->mockPodcastEpisodeListCall(1, 100, 200, false);
        $this->mockPodcastEpisodeListCall(2, 100, 200, false);

        $this->podcastEpisodes->reverse()->toArray();
    }
}
