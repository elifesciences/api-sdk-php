<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\People;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class PeopleTest extends ApiTestCase
{
    /** @var People */
    private $people;

    /**
     * @before
     */
    protected function setUpPeople()
    {
        $this->people = (new ApiSdk($this->getHttpClient()))->people();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->people);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockPersonListCall(1, 1, 200);
        $this->mockPersonListCall(1, 100, 200);
        $this->mockPersonListCall(2, 100, 200);

        foreach ($this->people as $i => $person) {
            $this->assertInstanceOf(Person::class, $person);
            $this->assertSame('person'.$i, $person->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockPersonListCall(1, 1, 10);

        $this->assertFalse($this->people->isEmpty());
        $this->assertSame(10, $this->people->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockPersonListCall(1, 1, 10);
        $this->mockPersonListCall(1, 100, 10);

        $array = $this->people->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $person) {
            $this->assertInstanceOf(Person::class, $person);
            $this->assertSame('person'.($i + 1), $person->getId());
        }
    }

    /**
     * @test
     */
    public function it_gets_a_person()
    {
        $this->mockPersonCall(7, true);

        $person = $this->people->get('person7')->wait();

        $this->assertInstanceOf(Person::class, $person);
        $this->assertSame('person7', $person->getId());

        $this->assertInstanceOf(Paragraph::class, $person->getProfile()->toArray()[0]);
        $this->assertSame('person7 profile text', $person->getProfile()->toArray()[0]->getText());

        $this->assertInstanceOf(Subject::class, $person->getResearch()->getExpertises()->toArray()[0]);
        $this->assertSame('Subject 1 name', $person->getResearch()->getExpertises()->toArray()[0]->getName());

        $this->mockSubjectCall(1);

        $this->assertSame('Subject subject1 impact statement',
            $person->getResearch()->getExpertises()->toArray()[0]->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_reuses_already_known_people()
    {
        $this->mockPersonListCall(1, 1, 1);
        $this->mockPersonListCall(1, 100, 1);

        $this->people->toArray();

        $person = $this->people->get('person1')->wait();

        $this->assertInstanceOf(Person::class, $person);
        $this->assertSame('person1', $person->getId());

        $this->mockPersonCall(1, true);

        $this->assertInstanceOf(Paragraph::class, $person->getProfile()->toArray()[0]);
        $this->assertSame('person1 profile text', $person->getProfile()->toArray()[0]->getText());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockPersonListCall(1, 1, 5, true, ['subject']);
        $this->mockPersonListCall(1, 100, 5, true, ['subject']);

        foreach ($this->people->forSubject('subject') as $i => $person) {
            $this->assertSame('person'.$i, $person->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_subject()
    {
        $this->mockPersonListCall(1, 1, 10);

        $this->people->count();

        $this->mockPersonListCall(1, 1, 10, true, ['subject']);

        $this->assertSame(10, $this->people->forSubject('subject')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_subject()
    {
        $this->mockPersonListCall(1, 1, 200);
        $this->mockPersonListCall(1, 100, 200);
        $this->mockPersonListCall(2, 100, 200);

        $this->people->toArray();

        $this->mockPersonListCall(1, 1, 200, true, ['subject']);
        $this->mockPersonListCall(1, 100, 200, true, ['subject']);
        $this->mockPersonListCall(2, 100, 200, true, ['subject']);

        $this->people->forSubject('subject')->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_type()
    {
        $this->mockPersonListCall(1, 1, 5, true, [], 'senior-editor');
        $this->mockPersonListCall(1, 100, 5, true, [], 'senior-editor');

        foreach ($this->people->forType('senior-editor') as $i => $person) {
            $this->assertSame('person'.$i, $person->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_type()
    {
        $this->mockPersonListCall(1, 1, 10);

        $this->people->count();

        $this->mockPersonListCall(1, 1, 10, true, [], 'senior-editor');

        $this->assertSame(10, $this->people->forType('senior-editor')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_type()
    {
        $this->mockPersonListCall(1, 1, 200);
        $this->mockPersonListCall(1, 100, 200);
        $this->mockPersonListCall(2, 100, 200);

        $this->people->toArray();

        $this->mockPersonListCall(1, 1, 200, true, [], 'senior-editor');
        $this->mockPersonListCall(1, 100, 200, true, [], 'senior-editor');
        $this->mockPersonListCall(2, 100, 200, true, [], 'senior-editor');

        $this->people->forType('senior-editor')->toArray();
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockPersonListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->people->slice($offset, $length) as $i => $person) {
            $this->assertInstanceOf(Person::class, $person);
            $this->assertSame('person'.($expected[$i]), $person->getId());
        }
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [
                1,
                1,
                [2],
                [
                    ['page' => 2, 'per-page' => 1],
                ],
            ],
            'offset -2, no length' => [
                -2,
                null,
                [4, 5],
                [
                    ['page' => 1, 'per-page' => 1],
                    ['page' => 1, 'per-page' => 100],
                ],
            ],
            'offset 6, no length' => [
                6,
                null,
                [],
                [
                    ['page' => 1, 'per-page' => 1],
                    ['page' => 1, 'per-page' => 100],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockPersonListCall(1, 1, 3);
        $this->mockPersonListCall(1, 100, 3);

        $map = function (Person $person) {
            return $person->getId();
        };

        $this->assertSame(['person1', 'person2', 'person3'], $this->people->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockPersonListCall(1, 1, 5);
        $this->mockPersonListCall(1, 100, 5);

        $filter = function (Person $person) {
            return substr($person->getId(), -1) > 3;
        };

        foreach ($this->people->filter($filter) as $i => $person) {
            $this->assertSame('person'.($i + 4), $person->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockPersonListCall(1, 1, 5);
        $this->mockPersonListCall(1, 100, 5);

        $reduce = function (int $carry = null, Person $person) {
            return $carry + substr($person->getId(), -1);
        };

        $this->assertSame(115, $this->people->reduce($reduce, 100)->wait());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockPersonListCall(1, 1, 5);
        $this->mockPersonListCall(1, 100, 5);

        $sort = function (Person $a, Person $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->people->sort($sort) as $i => $person) {
            $this->assertSame('person'.(5 - $i), $person->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockPersonListCall(1, 1, 5, false);
        $this->mockPersonListCall(1, 100, 5, false);

        foreach ($this->people->reverse() as $i => $person) {
            $this->assertSame('person'.$i, $person->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockPersonListCall(1, 1, 10);

        $this->people->count();

        $this->assertSame(10, $this->people->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockPersonListCall(1, 1, 200);
        $this->mockPersonListCall(1, 100, 200);
        $this->mockPersonListCall(2, 100, 200);

        $this->people->toArray();

        $this->mockPersonListCall(1, 1, 200, false);
        $this->mockPersonListCall(1, 100, 200, false);
        $this->mockPersonListCall(2, 100, 200, false);

        $this->people->reverse()->toArray();
    }
}
