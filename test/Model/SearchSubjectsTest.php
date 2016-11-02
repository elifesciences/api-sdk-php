<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\SearchSubjects;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\Builder;

class SearchSubjectsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_iterable_with_subjects_as_keys()
    {
        $searchSubjects = new SearchSubjects(
            [
                Builder::for(Subject::class)->sample('biophysics-structural-biology'),
                Builder::for(Subject::class)->sample('genomics-evolutionary-biology'),
            ],
            [10, 20]
        );
        foreach ($searchSubjects as $subject => $counter) {
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertInternalType('integer', $counter);
        }
    }
}
