<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class AppendixTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $appendix = new Appendix(
            'id',
            'title',
            new ArraySequence([
                new Section(
                    'Section title',
                    'id-section',
                    new ArraySequence([new Paragraph('Text')])
                ),
            ]),
            '10.7554/eLife.09560.app1'
        );

        $this->assertInstanceOf(HasId::class, $appendix);
        $this->assertSame('id', $appendix->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $appendix = new Appendix(
            'id',
            'title',
            new ArraySequence([
                new Section(
                    'Section title',
                    'id-section',
                    new ArraySequence([new Paragraph('Text')])
                ),
            ]),
            '10.7554/eLife.09560.app1'
        );

        $this->assertSame('title', $appendix->getTitle());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $appendix = new Appendix(
            'id',
            'title',
            $content = new ArraySequence([
                new Section(
                    'Section title',
                    'id-section',
                    new ArraySequence([new Paragraph('Text')])
                ),
            ]),
            '10.7554/eLife.09560.app1'
        );

        $this->assertEquals($content, $appendix->getContent());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new Appendix(
            'id',
            'title',
            new ArraySequence([
                new Section(
                    'Section title',
                    'id-section',
                    new ArraySequence([new Paragraph('Text')])
                ),
            ]),
            '10.7554/eLife.09560.app1'
        );
        $withOut = new Appendix(
            'id',
            'title',
            new ArraySequence([
                new Section(
                    'Section title',
                    'id-section',
                    new ArraySequence([new Paragraph('Text')])
                ),
            ])
        );

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.7554/eLife.09560.app1', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }
}
