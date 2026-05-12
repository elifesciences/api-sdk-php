<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\IntervieweeCvLine;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IntervieweeCvLineTest extends TestCase
{
    #[Test]
    public function it_has_a_date()
    {
        $cvLine = new IntervieweeCvLine('date', 'text');

        $this->assertSame('date', $cvLine->getDate());
    }

    #[Test]
    public function it_has_text()
    {
        $cvLine = new IntervieweeCvLine('date', 'text');

        $this->assertSame('text', $cvLine->getText());
    }
}
