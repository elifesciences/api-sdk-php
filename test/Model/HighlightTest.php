<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class HighlightTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_title()
    {
        $highlight = new Highlight('title', null, null, Builder::dummy(ArticleVoR::class));

        $this->assertSame('title', $highlight->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_author_line()
    {
        $with = new Highlight('title', 'author', null, Builder::dummy(ArticleVoR::class));
        $withOut = new Highlight('title', null, null, Builder::dummy(ArticleVoR::class));

        $this->assertSame('author', $with->getAuthorLine());
        $this->assertNull($withOut->getAuthorLine());
    }

    /**
     * @test
     */
    public function it_may_have_an_image()
    {
        $image = new Image('', [new ImageSize('16:9', [250 => 'https://placehold.it/250x141', 500 => 'https://placehold.it/500x281']), new ImageSize('1:1', ['70' => 'https://placehold.it/70x70', '140' => 'https://placehold.it/140x140'])]);

        $with = new Highlight('title', null, $image, Builder::dummy(ArticleVoR::class));
        $withOut = new Highlight('title', null, null, Builder::dummy(ArticleVoR::class));

        $this->assertEquals($image, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    public function it_has_an_item()
    {
        $highlight = new Highlight('title', null, null, $item = Builder::dummy(ArticleVoR::class));

        $this->assertSame($item, $highlight->getItem());
    }
}
