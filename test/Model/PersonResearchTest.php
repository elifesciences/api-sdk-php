<?php

namespace test\eLife\ApiSdk\Model;

use GuzzleHttp\Promise\Create;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\PersonResearch;
use eLife\ApiSdk\Model\Subject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

use PHPUnit\Framework\TestCase;

final class PersonResearchTest extends TestCase
{
    #[Test]
    #[DataProvider('subjectsProvider')]
    public function it_may_have_expertises(Sequence $expertises = null, array $expected)
    {
        $research = new PersonResearch($expertises, [], []);

        $this->assertEquals($expected, $research->getExpertises()->toArray());
    }

    public static function subjectsProvider() : array
    {
        $subjects = [
            new Subject('subject1', 'Subject 1', Create::rejectionFor('No impact statement'), new PromiseSequence(Create::rejectionFor('No aims and scope')),
                Create::rejectionFor('No banner'), Create::rejectionFor('No thumbnail')),
            new Subject('subject2', 'Subject 2', Create::rejectionFor('No impact statement'), new PromiseSequence(Create::rejectionFor('No aims and scope')),
                Create::rejectionFor('No banner'), Create::rejectionFor('No thumbnail')),
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

    #[Test]
    public function it_may_have_focuses()
    {
        $with = new PersonResearch(new PromiseSequence(Create::rejectionFor('Expertises should not be unwrapped')), ['focus'],
            []);
        $withOut = new PersonResearch(new PromiseSequence(Create::rejectionFor('Expertises should not be unwrapped')), [], []);

        $this->assertSame(['focus'], $with->getFocuses());
        $this->assertEmpty($withOut->getFocuses());
    }

    #[Test]
    public function it_may_have_organisms()
    {
        $with = new PersonResearch(new PromiseSequence(Create::rejectionFor('Expertises should not be unwrapped')), [],
            ['organism']);
        $withOut = new PersonResearch(new PromiseSequence(Create::rejectionFor('Expertises should not be unwrapped')), [], []);

        $this->assertSame(['organism'], $with->getOrganisms());
        $this->assertEmpty($withOut->getOrganisms());
    }
}
