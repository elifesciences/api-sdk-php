<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ArticlePoATest extends PHPUnit_Framework_TestCase
{
    /** @var Builder */
    protected $builder;

    public function setUp()
    {
        $this->builder = Builder::for(ArticlePoA::class);
    }

    /**
     * @test
     */
    final public function it_is_a_model()
    {
        $article = $this->builder->__invoke();

        $this->assertInstanceOf(Model::class, $article);
    }

    /**
     * @test
     */
    final public function it_has_an_id()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('14107', $article->getId());
    }

    /**
     * @test
     */
    final public function it_has_a_version()
    {
        $article = $this->builder->__invoke();

        $this->assertSame(1, $article->getVersion());
    }

    /**
     * @test
     */
    final public function it_has_a_type()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('research-article', $article->getType());
    }

    /**
     * @test
     */
    final public function it_has_a_doi()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('10.7554/eLife.14107', $article->getDoi());
    }

    /**
     * @test
     */
    final public function it_has_an_author_line()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('Yongjian Huang et al', $article->getAuthorLine());
    }

    /**
     * @test
     */
    final public function it_may_have_a_title_prefix()
    {
        $withOut = $this->builder->__invoke();
        $with = $this->builder->withTitlePrefix('title prefix')->__invoke();

        $this->assertSame('title prefix', $with->getTitlePrefix());
        $this->assertSame('title prefix: Molecular basis for multimerization in the activation of the epidermal growth factor', $with->getFullTitle());
        $this->assertNull($withOut->getTitlePrefix());
        $this->assertSame('Molecular basis for multimerization in the activation of the epidermal growth factor', $withOut->getFullTitle());
    }

    /**
     * @test
     */
    final public function it_has_a_title()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('Molecular basis for multimerization in the activation of the epidermal growth factor', $article->getTitle());
    }

    /**
     * @test
     */
    final public function it_has_a_published_date()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new DateTimeImmutable('2016-03-28T00:00:00+00:00'), $article->getPublishedDate());
    }

    /**
     * @test
     */
    final public function it_has_a_status_date()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new DateTimeImmutable('2016-03-28T00:00:00+00:00'), $article->getStatusDate());
    }

    /**
     * @test
     */
    final public function it_has_a_volume()
    {
        $article = $this->builder->__invoke();

        $this->assertSame(5, $article->getVolume());
    }

    /**
     * @test
     */
    final public function it_has_an_elocation_id()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('e14107', $article->getElocationId());
    }

    /**
     * @test
     */
    final public function it_may_have_a_pdf()
    {
        $withOut = $this->builder->__invoke();
        $with = $this->builder->withPdf('http://www.example.com/article14107.pdf')->__invoke();

        $this->assertSame('http://www.example.com/article14107.pdf', $with->getPdf());
        $this->assertNull($withOut->getPdf());
    }

    /**
     * @test
     */
    final public function it_may_have_subjects()
    {
        $subjects = new ArraySequence([
            Builder::for(Subject::class)
                ->withId('subject1')
                ->__invoke(),
        ]);

        $article = $this->builder->withSubjects($subjects)->__invoke();

        $this->assertEquals($subjects, $article->getSubjects());
    }

    /**
     * @test
     */
    final public function it_may_have_research_organisms()
    {
        $withOut = $this->builder->__invoke();
        $with = $this->builder->withResearchOrganisms(['research organism'])->__invoke();

        $this->assertSame(['research organism'], $with->getResearchOrganisms());
        $this->assertEmpty($withOut->getResearchOrganisms());
    }

    /**
     * @test
     */
    final public function it_may_have_an_abstract()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withPromiseOfAbstract(null)->__invoke();

        $this->assertEquals(new ArticleSection(new ArraySequence([new Paragraph('Article 14107 abstract text')])), $with->getAbstract());
        $this->assertNull($withOut->getAbstract());
    }

    /**
     * @test
     */
    final public function it_may_have_an_issue()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withPromiseOfIssue(null)->__invoke();

        $this->assertEquals(1, $with->getIssue());
        $this->assertNull($withOut->getIssue());
    }

    /**
     * @test
     */
    final public function it_has_a_copyright()
    {
        $article = $this->builder->withCopyright($copyright = new Copyright('CC-BY-4.0', 'Statement', 'Author et al'))->__invoke();

        $this->assertEquals($copyright, $article->getCopyright());
    }

    /**
     * @test
     */
    final public function it_has_authors()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))]), $article->getAuthors());
    }
}
