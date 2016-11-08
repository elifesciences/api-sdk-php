<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\PersonDetails;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class InterviewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertSame('id', $interview->getId());
    }

    /**
     * @test
     */
    public function it_has_an_interviewee()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertEquals($interviewee, $interview->getInterviewee());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertSame('title', $interview->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_sub_title()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertSame('An interview with preferred name', $interview->getSubTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $intervieweeWith = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $intervieweeWithOut = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));

        $with = new Interview('id', $intervieweeWith, 'title', new DateTimeImmutable(), 'impact statement',
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $intervieweeWithOut, 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', $date = new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertEquals($date, $interview->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));

        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable(), null,
            new ArraySequence($content)
        );

        $this->assertEquals($content, $interview->getContent()->toArray());
    }
}
