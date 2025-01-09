<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\ElifeAssessment;
use PHPUnit\Framework\TestCase;

final class ElifeAssessmentTest extends TestCase
{
    private $title = 'eLife Assessment';

    /**
     * @test
     */
    public function it_may_have_significance_terms()
    {
        $with = new ElifeAssessment($this->title, ['important'], ['solid']);
        $withOut = new ElifeAssessment($this->title, null, ['solid']);

        $this->assertSame(['important'], $with->getSignificance());
        $this->assertNull($withOut->getSignificance());
    }

    /**
     * @test
     */
    public function it_may_have_strength_terms()
    {
        $with = new ElifeAssessment($this->title, ['important'], ['solid']);
        $withOut = new ElifeAssessment($this->title, ['important'], null);

        $this->assertSame(['solid'], $with->getStrength());
        $this->assertNull($withOut->getStrength());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $assessment = new ElifeAssessment($this->title, null, null);

        $this->assertSame($this->title, $assessment->getTitle());
    }
}
