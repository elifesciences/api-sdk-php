<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\Image;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class CoverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_title()
    {
        $image = Builder::for(Image::class)->sample('banner');
        $cover = new Cover('title', null, $image, Builder::dummy(ArticleVoR::class));

        $this->assertSame('title', $cover->getTitle());
    }
    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $image = Builder::for(Image::class)->sample('banner');
        $with = new Cover('title', 'impact statement', $image, Builder::dummy(ArticleVoR::class));
        $withOut = new Cover('title', null, $image, Builder::dummy(ArticleVoR::class));

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $image = Builder::for(Image::class)->sample('banner');
        $cover = new Cover('title', null, $image, Builder::dummy(ArticleVoR::class));

        $this->assertInstanceOf(HasBanner::class, $cover);
        $this->assertEquals($image, $cover->getBanner());
    }

    /**
     * @test
     */
    public function it_has_an_item()
    {
        $image = Builder::for(Image::class)->sample('banner');
        $cover = new Cover('title', null, $image, $item = Builder::dummy(ArticleVoR::class));

        $this->assertEquals($item, $cover->getItem());
    }
}
