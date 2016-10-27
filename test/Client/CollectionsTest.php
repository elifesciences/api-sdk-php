<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\BlogArticle;
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

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame('tropical-disease', $collection->getId());

        $this->assertInstanceOf(BlogArticle::class, $collection->getContent()->toArray()[0]);
        $this->assertSame('Media coverage: Slime can see', $collection->getContent()->toArray()[0]->getTitle());

        $this->assertInstanceOf(Subject::class, $collection->getSubjects()->toArray()[0]);
        $this->assertSame('Subject 1 name', $collection->getSubjects()->toArray()[0]->getName());

        $this->mockSubjectCall('1');
        $this->mockSubjectCall('biophysics-structural-biology');

        $this->assertSame('Subject 1 impact statement',
            $collection->getSubjects()->toArray()[0]->getImpactStatement());
    }
}
