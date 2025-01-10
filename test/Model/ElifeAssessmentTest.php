<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\ApiSdk\Model\Block\Paragraph;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class ElifeAssessmentTest extends TestCase
{
    private $builder;

    /**
     * @before
     */
    public function set_up()
    {
        $this->builder = Builder::for(ElifeAssessment::class);
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $title = 'eLife Assessment';
        $assessment = $this->builder
            ->withTitle($title)
            ->__invoke();

        $this->assertSame($title, $assessment->getTitle());
    }

    /**
     * @test
     */
    public function it_has_an_article_section()
    {
        $articleSection = new ArticleSection(new ArraySequence([new Paragraph('eLife assessment')]));
        $assessment = $this->builder
            ->withArticleSection($articleSection)
            ->__invoke();

        $this->assertSame($articleSection, $assessment->getArticleSection());
    }

    /**
     * @test
     */
    public function it_may_have_significance_terms()
    {
        $with = $this->builder
            ->withSignificance(['important'])
            ->__invoke();
        $withOut = $this->builder
            ->withSignificance(null)
            ->__invoke();

        $this->assertSame(['important'], $with->getSignificance());
        $this->assertNull($withOut->getSignificance());
    }

    /**
     * @test
     */
    public function it_may_have_strength_terms()
    {
        $with = $this->builder
            ->withStrength(['solid'])
            ->__invoke();
        $withOut = $this->builder
            ->withStrength(null)
            ->__invoke();

        $this->assertSame(['solid'], $with->getStrength());
        $this->assertNull($withOut->getStrength());
    }
}
