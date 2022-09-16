<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\ReviewedPreprint;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ReviewedPreprintTest extends PHPUnit_Framework_TestCase
{
    private $builder;

    public function setUp()
    {
        $this->builder = Builder::for(ReviewedPreprint::class);
    }

    /**
     * @test
     */
    public function it_is_a_model()
    {
        $reviewedPreprint = $this->builder->__invoke();

        $this->assertInstanceOf(Model::class, $reviewedPreprint);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reviewedPreprint = $this->builder
            ->withId('14107')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $reviewedPreprint);
        $this->assertSame('14107', $reviewedPreprint->getId());
    }

    /**
     * @test
     */
    final public function it_has_a_stage()
    {
        $reviewedPreprint = $this->builder
            ->withStage(ReviewedPreprint::STAGE_PREVIEW)
            ->__invoke();

        $this->assertSame(ReviewedPreprint::STAGE_PREVIEW, $reviewedPreprint->getStage());
    }

    /**
     * @test
     */
    final public function it_may_have_a_doi()
    {
        $with = $this->builder
            ->withDoi('10.7554/eLife.14107')
            ->__invoke();
        $withOut = $this->builder
            ->withDoi(null)
            ->__invoke();

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.7554/eLife.14107', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    // @todo - it may have an authorLine
    // @todo - it may have a titlePrefix
    // @todo - plus more...

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reviewedPreprint = $this->builder
            ->withTitle('title')
            ->__invoke();

        $this->assertSame('title', $reviewedPreprint->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_status()
    {
        $reviewedPreprint = $this->builder
            ->withStatus('status')
            ->__invoke();

        $this->assertSame('status', $reviewedPreprint->getStatus());
    }

    /**
     * @test
     */
    final public function it_may_have_a_volume()
    {
        $with = $this->builder
            ->withVolume(5)
            ->__invoke();
        $withOut = $this->builder
            ->withVolume(null)
            ->__invoke();

        $this->assertSame(5, $with->getVolume());
        $this->assertNull($withOut->getVolume());
    }

    /**
     * @test
     */
    final public function it_may_have_an_elocation_id()
    {
        $with = $this->builder
            ->withElocationId('e14107')
            ->__invoke();
        $withOut = $this->builder
            ->withElocationId(null)
            ->__invoke();

        $this->assertSame('e14107', $with->getElocationId());
        $this->assertNull($withOut->getElocationId());
    }
}
