<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\PersonResearch;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class PersonResearchTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_expertises(Sequence $expertises = null, array $expected)
    {
        $research = new PersonResearch($expertises, [], []);

        $this->assertEquals($expected, $research->getExpertises()->toArray());
    }

    public function subjectsProvider() : array
    {
        $subjects = [
            new Subject('subject1', 'Subject 1', rejection_for('No impact statement'), rejection_for('No banner'),
                rejection_for('No thumbnail')),
            new Subject('subject2', 'Subject 2', rejection_for('No impact statement'), rejection_for('No banner'),
                rejection_for('No thumbnail')),
        ];

        return [
            'none' => [
                new EmptySequence(),
                [],
            ],
            'collection' => [
                new ArraySequence($subjects),
                $subjects,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_may_have_focuses()
    {
        $with = new PersonResearch(new PromiseSequence(rejection_for('Expertises should not be unwrapped')), ['focus'],
            []);
        $withOut = new PersonResearch(new PromiseSequence(rejection_for('Expertises should not be unwrapped')), [], []);

        $this->assertSame(['focus'], $with->getFocuses());
        $this->assertEmpty($withOut->getFocuses());
    }

    /**
     * @test
     */
    public function it_may_have_organisms()
    {
        $with = new PersonResearch(new PromiseSequence(rejection_for('Expertises should not be unwrapped')), [],
            ['organism']);
        $withOut = new PersonResearch(new PromiseSequence(rejection_for('Expertises should not be unwrapped')), [], []);

        $this->assertSame(['organism'], $with->getOrganisms());
        $this->assertEmpty($withOut->getOrganisms());
    }
}
