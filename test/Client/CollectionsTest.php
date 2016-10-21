<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Collections;
#use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class CollectionsTest extends ApiTestCase
{
    /** @var Collections */
    private $collections;

    /**
     * @before
     */
    protected function setUpCollections()
    {
        $this->collections = (new ApiSdk($this->getHttpClient()))->collections();
    }

    /**
     * @test
     */
    public function it_gets_a_collection()
    {
        $this->mockCollectionCall('tropical-disease', true);

        $collection = $this->collections->get('tropical-disease')->wait();

        return;
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
}
