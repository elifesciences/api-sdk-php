<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\PersonDetails;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class IntervieweeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_person()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));

        $this->assertEquals($person, $interviewee->getPerson());
    }

    /**
     * @test
     * @dataProvider cvLinesProvider
     */
    public function it_may_have_cv_lines(Sequence $cvLines, array $expected)
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person, $cvLines);

        $this->assertEquals($expected, $interviewee->getCvLines()->toArray());
    }

    public function cvLinesProvider() : array
    {
        $cvLines = [new IntervieweeCvLine('date', 'text')];

        return [
            'none' => [
                new ArraySequence([]),
                [],
            ],
            'collection' => [
                new ArraySequence($cvLines),
                $cvLines,
            ],
        ];
    }
}
