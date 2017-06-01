<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Image;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class HighlightTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_title()
    {
        $highlight = new Highlight('title', null, Builder::dummy(ArticleVoR::class));

        $this->assertSame('title', $highlight->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_image()
    {
        $image = Builder::for(Image::class)->sample('thumbnail');

        $with = new Highlight('title', $image, Builder::dummy(ArticleVoR::class));
        $withOut = new Highlight('title', null, Builder::dummy(ArticleVoR::class));

        $this->assertEquals($image, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    public function it_has_an_item()
    {
        $highlight = new Highlight('title', null, $item = Builder::dummy(ArticleVoR::class));

        $this->assertSame($item, $highlight->getItem());
    }
}
