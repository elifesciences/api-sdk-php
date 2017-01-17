<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Footnote;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class FootnoteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Footnote('id', null, new ArraySequence([new Paragraph('footnote')]));
        $withOut = new Footnote(null, null, new ArraySequence([new Paragraph('footnote')]));

        $this->assertInstanceOf(HasId::class, $with);
        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new Footnote(null, 'label', new ArraySequence([new Paragraph('footnote')]));
        $withOut = new Footnote(null, null, new ArraySequence([new Paragraph('footnote')]));

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_has_text()
    {
        $footnote = new Footnote(null, null, $text = new ArraySequence([new Paragraph('footnote')]));

        $this->assertEquals($text, $footnote->getText());
    }
}
