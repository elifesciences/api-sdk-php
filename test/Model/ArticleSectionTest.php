<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\Block\Paragraph;
use PHPUnit_Framework_TestCase;

final class ArticleSectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_content()
    {
        $content = new ArraySequence([new Paragraph('content')]);
        $articleSection = new ArticleSection($content);

        $this->assertEquals($content, $articleSection->getContent());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new ArticleSection(new ArraySequence([]), '10.1000/182');
        $withOut = new ArticleSection(new ArraySequence([]));

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }
}