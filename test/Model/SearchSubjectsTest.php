<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\SearchSubjects;
use eLife\ApiSdk\Model\Subject;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

class SearchSubjectsTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_iterable_with_subjects_as_keys()
    {
        $searchSubjects = new SearchSubjects(
            $expectedSubjects = [
                Builder::for(Subject::class)->sample('biophysics-structural-biology'),
                Builder::for(Subject::class)->sample('genomics-evolutionary-biology'),
            ],
            $expectedResults = [10, 20]
        );

        $actualSubjects = [];
        $actualResults = [];
        foreach ($searchSubjects as $subject => $results) {
            $this->assertInstanceOf(Subject::class, $subject);
            $actualSubjects[] = $subject;
            $this->assertInternalType('integer', $results);
            $actualResults[] = $results;
        }

        $this->assertSame($expectedSubjects, $actualSubjects);
        $this->assertSame($expectedResults, $actualResults);
    }
}
