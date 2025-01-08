<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\ElifeAssessment;
use PHPUnit\Framework\TestCase;

final class ElifeAssessmentTest extends TestCase
{

    /**
     * @test
     */
    public function it_may_have_significance_terms()
    {
        $with = new ElifeAssessment(null, ['important'], ['solid']);
        $withOut = new ElifeAssessment(null, null, ['solid']);

        $this->assertSame(['important'], $with->getSignificance());
        $this->assertNull($withOut->getSignificance());
    }

    /**
     * @test
     */
    public function it_may_have_strength_terms()
    {
        $with = new ElifeAssessment(null, ['important'], ['solid']);
        $withOut = new ElifeAssessment(null, ['important'], null);

        $this->assertSame(['solid'], $with->getStrength());
        $this->assertNull($withOut->getStrength());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new ElifeAssessment('Lorem ipsum', null, null);
        $withOut = new ElifeAssessment(null, null, null);

        $this->assertSame('Lorem ipsum', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }
}
