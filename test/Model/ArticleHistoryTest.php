<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\ArticleHistory;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticlePreprint;
use eLife\ApiSdk\Model\Date;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ArticleHistoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_may_have_a_received_date()
    {
        $with = new ArticleHistory($date = Date::fromString('2016-01-01'), null, null, new ArraySequence([Builder::dummy(ArticlePoA::class)]));
        $withOut = new ArticleHistory(null, null, null, new EmptySequence());

        $this->assertEquals($date, $with->getReceived());
        $this->assertNull($withOut->getReceived());
    }

    /**
     * @test
     */
    public function it_may_have_an_accepted_date()
    {
        $with = new ArticleHistory(null, $date = Date::fromString('2016-01-01'), null, new ArraySequence([Builder::dummy(ArticlePoA::class)]));
        $withOut = new ArticleHistory(null, null, null, new EmptySequence());

        $this->assertEquals($date, $with->getAccepted());
        $this->assertNull($withOut->getAccepted());
    }

    /**
     * @test
     */
    public function it_may_have_a_sent_for_review_date()
    {
        $with = new ArticleHistory(null, null, $date = Date::fromString('2016-01-01'), new ArraySequence([Builder::dummy(ArticlePoA::class)]));
        $withOut = new ArticleHistory(null, null, null, new EmptySequence());

        $this->assertEquals($date, $with->getSentForReview());
        $this->assertNull($withOut->getSentForReview());
    }

    /**
     * @test
     */
    public function it_has_versions()
    {
        $history = new ArticleHistory(null, null, null, $versions = new ArraySequence([Builder::dummy(ArticlePoA::class), Builder::dummy(ArticlePreprint::class)]));

        $this->assertEquals($versions, $history->getVersions());
    }
}
