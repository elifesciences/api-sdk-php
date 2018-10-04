<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class SubjectsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Subjects */
    private $subjects;

    /**
     * @before
     */
    protected function setUpSubjects()
    {
        $this->subjects = (new ApiSdk($this->getHttpClient()))->subjects();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->subjects);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockSubjectListCall(1, 1, 200);
        $this->mockSubjectListCall(1, 100, 200);
        $this->mockSubjectListCall(2, 100, 200);

        foreach ($this->subjects as $i => $subject) {
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame('subject'.$i, $subject->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockSubjectListCall(1, 1, 10);

        $this->assertFalse($this->subjects->isEmpty());
        $this->assertSame(10, $this->subjects->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockSubjectListCall(1, 1, 10);
        $this->mockSubjectListCall(1, 100, 10);

        $array = $this->subjects->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $subject) {
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame('subject'.($i + 1), $subject->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockSubjectListCall(1, 1, 1);

        $this->assertTrue(isset($this->subjects[0]));
        $this->assertSame('subject1', $this->subjects[0]->getId());

        $this->mockNotFound(
            'subjects?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT_LIST, 1)]
        );

        $this->assertFalse(isset($this->subjects[5]));
        $this->assertSame(null, $this->subjects[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->subjects[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_subject()
    {
        $this->mockSubjectCall(7);

        $subject = $this->subjects->get('subject7')->wait();

        $this->assertInstanceOf(Subject::class, $subject);
        $this->assertSame('subject7', $subject->getId());
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockSubjectListCall(1, 1, 5);
        $this->mockSubjectListCall(1, 100, 5);

        $values = $this->subjects->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'subject1', 'subject2', 'subject3', 'subject4', 'subject5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockSubjectListCall(1, 1, 5);
        $this->mockSubjectListCall(1, 100, 5);

        $values = $this->subjects->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['subject1', 'subject2', 'subject3', 'subject4', 'subject5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockSubjectListCall(1, 1, 5);
        $this->mockSubjectListCall(1, 100, 5);

        $values = $this->subjects->drop(2)->map($this->tidyValue());

        $this->assertSame(['subject1', 'subject2', 'subject4', 'subject5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockSubjectListCall(1, 1, 5);
        $this->mockSubjectListCall(1, 100, 5);

        $values = $this->subjects->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['subject1', 'subject2', 2, 'subject3', 'subject4', 'subject5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockSubjectListCall(1, 1, 5);
        $this->mockSubjectListCall(1, 100, 5);

        $values = $this->subjects->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['subject1', 'subject2', 2, 'subject4', 'subject5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockSubjectListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->subjects->slice($offset, $length) as $i => $subject) {
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame('subject'.($expected[$i]), $subject->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockSubjectListCall(1, 1, 3);
        $this->mockSubjectListCall(1, 100, 3);

        $map = function (Subject $subject) {
            return $subject->getId();
        };

        $this->assertSame(['subject1', 'subject2', 'subject3'], $this->subjects->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockSubjectListCall(1, 1, 5);
        $this->mockSubjectListCall(1, 100, 5);

        $filter = function (Subject $subject) {
            return substr($subject->getId(), -1) > 3;
        };

        foreach ($this->subjects->filter($filter) as $i => $subject) {
            $this->assertSame('subject'.($i + 4), $subject->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockSubjectListCall(1, 1, 5);
        $this->mockSubjectListCall(1, 100, 5);

        $reduce = function (int $carry = null, Subject $subject) {
            return $carry + substr($subject->getId(), -1);
        };

        $this->assertSame(115, $this->subjects->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->subjects, $this->subjects->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockSubjectListCall(1, 1, 5);
        $this->mockSubjectListCall(1, 100, 5);

        $sort = function (Subject $a, Subject $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->subjects->sort($sort) as $i => $subject) {
            $this->assertSame('subject'.(5 - $i), $subject->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockSubjectListCall(1, 1, 5, false);
        $this->mockSubjectListCall(1, 100, 5, false);

        foreach ($this->subjects->reverse() as $i => $subject) {
            $this->assertSame('subject'.$i, $subject->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockSubjectListCall(1, 1, 10);

        $this->subjects->count();

        $this->assertSame(10, $this->subjects->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockSubjectListCall(1, 1, 200);
        $this->mockSubjectListCall(1, 100, 200);
        $this->mockSubjectListCall(2, 100, 200);

        $this->subjects->toArray();

        $this->mockSubjectListCall(1, 1, 200, false);
        $this->mockSubjectListCall(1, 100, 200, false);
        $this->mockSubjectListCall(2, 100, 200, false);

        $this->subjects->reverse()->toArray();
    }
}
