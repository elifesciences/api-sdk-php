<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\IntervieweeCvLine;
use PHPUnit_Framework_TestCase;

final class IntervieweeCvLineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_date()
    {
        $cvLine = new IntervieweeCvLine('date', 'text');

        $this->assertSame('date', $cvLine->getDate());
    }

    /**
     * @test
     */
    public function it_has_text()
    {
        $cvLine = new IntervieweeCvLine('date', 'text');

        $this->assertSame('text', $cvLine->getText());
    }
}
