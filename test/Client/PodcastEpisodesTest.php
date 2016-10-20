<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class PodcastEpisodesTest extends ApiTestCase
{
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
    public function it_is_a_collection()
    {
        $this->assertInstanceOf(Collection::class, $this->podcastEpisodes);
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
    public function it_gets_a_podcast_episode()
    {
        $this->mockPodcastEpisodeCall(7, true);

        $podcastEpisode = $this->podcastEpisodes->get(7)->wait();

        $this->assertInstanceOf(PodcastEpisode::class, $podcastEpisode);
        $this->assertSame(7, $podcastEpisode->getNumber());

        $this->assertInstanceOf(PodcastEpisodeChapter::class, $podcastEpisode->getChapters()->toArray()[0]);
        $this->assertSame('Chapter title', $podcastEpisode->getChapters()->toArray()[0]->getTitle());

        $this->assertInstanceOf(Subject::class, $podcastEpisode->getSubjects()->toArray()[0]);
        $this->assertSame('Subject 1 name', $podcastEpisode->getSubjects()->toArray()[0]->getName());

        $this->mockSubjectCall(1);

        $this->assertSame('Subject 1 impact statement',
            $podcastEpisode->getSubjects()->toArray()[0]->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_reuses_already_known_podcast_episodes()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 1);
        $this->mockPodcastEpisodeListCall(1, 100, 1);

        $this->podcastEpisodes->toArray();

        $podcastEpisode = $this->podcastEpisodes->get(1)->wait();

        $this->assertInstanceOf(PodcastEpisode::class, $podcastEpisode);
        $this->assertSame(1, $podcastEpisode->getNumber());

        $this->mockPodcastEpisodeCall(1);

        $this->assertInstanceOf(PodcastEpisodeChapter::class, $podcastEpisode->getChapters()->toArray()[0]);
        $this->assertSame('Chapter title', $podcastEpisode->getChapters()->toArray()[0]->getTitle());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 5, true, ['subject']);
        $this->mockPodcastEpisodeListCall(1, 100, 5, true, ['subject']);

        foreach ($this->podcastEpisodes->forSubject('subject') as $i => $podcastEpisode) {
            $this->assertSame($i, $podcastEpisode->getNumber());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_subject()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 10);

        $this->podcastEpisodes->count();

        $this->mockPodcastEpisodeListCall(1, 1, 10, true, ['subject']);

        $this->assertSame(10, $this->podcastEpisodes->forSubject('subject')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_subject()
    {
        $this->mockPodcastEpisodeListCall(1, 1, 200);
        $this->mockPodcastEpisodeListCall(1, 100, 200);
        $this->mockPodcastEpisodeListCall(2, 100, 200);

        $this->podcastEpisodes->toArray();

        $this->mockPodcastEpisodeListCall(1, 1, 200, true, ['subject']);
        $this->mockPodcastEpisodeListCall(1, 100, 200, true, ['subject']);
        $this->mockPodcastEpisodeListCall(2, 100, 200, true, ['subject']);

        $this->podcastEpisodes->forSubject('subject')->toArray();
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

        $this->assertSame(115, $this->podcastEpisodes->reduce($reduce, 100)->wait());
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
