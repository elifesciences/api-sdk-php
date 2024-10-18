<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\PublicReview;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use PHPUnit\Framework\TestCase;

final class PublicReviewTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_title()
    {
        $title = 'title';
        $publicReview = new PublicReview($title, new EmptySequence());

        $this->assertEquals($title, $publicReview->getTitle());
    }
    /**
     * @test
     */
    public function it_has_content()
    {
        $content = new ArraySequence([new Paragraph('content')]);
        $articleSection = new PublicReview('title', $content);

        $this->assertEquals($content, $articleSection->getContent());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new PublicReview('title', new EmptySequence(), '10.1000/182');
        $withOut = new PublicReview('title', new EmptySequence());

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new PublicReview('title', new EmptySequence(), null, 'id');
        $withOut = new PublicReview('title', new EmptySequence());

        $this->assertInstanceOf(HasId::class, $with);
        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }
}
