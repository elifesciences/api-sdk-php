<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Person;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class IntervieweeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_person()
    {
        $person = new Person('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));

        $this->assertEquals($person, $interviewee->getPerson());
    }

    /**
     * @test
     * @dataProvider cvLinesProvider
     */
    public function it_may_have_cv_lines(Collection $cvLines = null, bool $hasCvLines, array $expected)
    {
        $person = new Person('preferred name', 'index name');
        $interviewee = new Interviewee($person, $cvLines);

        $this->assertSame($hasCvLines, $interviewee->hasCvLines());
        $this->assertEquals($expected, $interviewee->getCvLines()->toArray());
    }

    public function cvLinesProvider() : array
    {
        $cvLines = [new IntervieweeCvLine('date', 'text')];

        return [
            'none' => [
                null,
                false,
                [],
            ],
            'collection' => [
                new ArraySequence($cvLines),
                true,
                $cvLines,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_not_unwrap_cv_lines_when_checking_if_it_has_any()
    {
        $person = new Person('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('CV lines should not be unwrapped')));

        $this->assertTrue($interviewee->hasCvLines());
    }
}
