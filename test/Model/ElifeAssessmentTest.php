<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\ApiSdk\Model\Block\Paragraph;
use PHPUnit\Framework\TestCase;

final class ElifeAssessmentTest extends TestCase
{
    private $title = 'eLife Assessment';
    private $articleSection;

    /**
     * @before
     */
    public function set_up()
    {
        $this->articleSection = new ArticleSection(new ArraySequence([new Paragraph('eLife assessment')]));
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $assessment = new ElifeAssessment($this->title, null, null, null);

        $this->assertSame($this->title, $assessment->getTitle());
    }

    /**
     * @test
     */
    public function it_has_an_article_section()
    {
        $assessment = new ElifeAssessment($this->title, $this->articleSection, null, null);

        $this->assertSame($this->articleSection, $assessment->getArticleSection());
    }

    /**
     * @test
     */
    public function it_may_have_significance_terms()
    {
        $with = new ElifeAssessment($this->title, null, ['important'], ['solid']);
        $withOut = new ElifeAssessment($this->title, null, null, ['solid']);

        $this->assertSame(['important'], $with->getSignificance());
        $this->assertNull($withOut->getSignificance());
    }

    /**
     * @test
     */
    public function it_may_have_strength_terms()
    {
        $with = new ElifeAssessment($this->title, null, ['important'], ['solid']);
        $withOut = new ElifeAssessment($this->title, null, ['important'], null);

        $this->assertSame(['solid'], $with->getStrength());
        $this->assertNull($withOut->getStrength());
    }
}
