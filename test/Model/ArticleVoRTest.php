<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ArticleVoRTest extends PHPUnit_Framework_TestCase
{
    /** @var Builder */
    protected $builder;

    public function setUp()
    {
        $this->builder = Builder::for(ArticleVoR::class);
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

        $this->assertSame('09560', $article->getId());
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

        $this->assertSame('10.7554/eLife.09560', $article->getDoi());
    }

    /**
     * @test
     */
    final public function it_has_an_author_line()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('Lee R Berger et al', $article->getAuthorLine());
    }

    /**
     * @test
     */
    final public function it_may_have_a_title_prefix()
    {
        $withOut = $this->builder->__invoke();
        $with = $this->builder->withTitlePrefix('title prefix')->__invoke();

        $this->assertSame('title prefix', $with->getTitlePrefix());
        $this->assertSame('title prefix: <i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa', $with->getFullTitle());
        $this->assertNull($withOut->getTitlePrefix());
        $this->assertSame('<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa', $withOut->getFullTitle());
    }

    /**
     * @test
     */
    final public function it_has_a_title()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa', $article->getTitle());
    }

    /**
     * @test
     */
    final public function it_has_a_published_date()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new DateTimeImmutable('2015-09-10T00:00:00Z'), $article->getPublishedDate());
    }

    /**
     * @test
     */
    final public function it_has_a_status_date()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new DateTimeImmutable('2015-09-10T00:00:00Z'), $article->getStatusDate());
    }

    /**
     * @test
     */
    final public function it_has_a_volume()
    {
        $article = $this->builder->__invoke();

        $this->assertSame(4, $article->getVolume());
    }

    /**
     * @test
     */
    final public function it_has_an_elocation_id()
    {
        $article = $this->builder->__invoke();

        $this->assertSame('e09560', $article->getElocationId());
    }

    /**
     * @test
     */
    final public function it_may_have_a_pdf()
    {
        $withOut = $this->builder->__invoke();
        $with = $this->builder->withPdf('http://www.example.com/article.pdf')->__invoke();

        $this->assertSame('http://www.example.com/article.pdf', $with->getPdf());
        $this->assertNull($withOut->getPdf());
    }

    /**
     * @test
     */
    final public function it_may_have_subjects()
    {
        $subjects = new ArraySequence([Builder::for(Subject::class)
            ->withId('subject1')
            ->__invoke(), ]);

        $article = $this->builder->withSubjects($subjects)->__invoke();

        $this->assertEquals($subjects, $article->getSubjects());
    }

    /**
     * @test
     */
    final public function it_may_have_research_organisms()
    {
        $withOut = $this->builder->__invoke();
        $with = $this->builder->withResearchOrganisms(['organism'])->__invoke();

        $this->assertSame(['organism'], $with->getResearchOrganisms());
        $this->assertEmpty($withOut->getResearchOrganisms());
    }

    /**
     * @test
     */
    final public function it_may_have_an_abstract()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withPromiseOfAbstract(null)->__invoke();

        $this->assertEquals(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 abstract text')])), $with->getAbstract());
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
        $article = $this->builder->__invoke();

        $this->assertEquals(new Copyright('CC-BY-4.0', 'Statement', 'Author et al'), $article->getCopyright());
    }

    /**
     * @test
     */
    final public function it_has_authors()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))]), $article->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withImpactStatement(null)->__invoke();

        $this->assertSame('A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_a_banner()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withPromiseOfBanner(null)->__invoke();

        $this->assertEquals(new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900'])]), $with->getBanner());
        $this->assertNull($withOut->getBanner());
    }

    /**
     * @test
     */
    public function it_may_have_a_thumbnail()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withThumbnail(null)->__invoke();

        $this->assertEquals(new Image('', [new ImageSize('16:9', [250 => 'https://placehold.it/250x141', 500 => 'https://placehold.it/500x281']), new ImageSize('1:1', ['70' => 'https://placehold.it/70x70', '140' => 'https://placehold.it/140x140'])]), $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_keywords()
    {
        $article = $this->builder->withKeywords($keywords = new ArraySequence(['keyword']))->__invoke();

        $this->assertEquals($keywords, $article->getKeywords());
    }

    /**
     * @test
     */
    public function it_may_have_a_digest()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withPromiseOfDigest(null)->__invoke();

        $this->assertEquals(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 digest')]), '10.7554/eLife.09560digest'), $with->getDigest());
        $this->assertNull($withOut->getDigest());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new ArraySequence([new Section('Article 09560 section title', 'article09560section', [new Paragraph('Article 09560 text')])]), $article->getContent());
    }

    /**
     * @test
     */
    public function it_may_have_appendices()
    {
        $appendices = new ArraySequence([
            new Appendix(
                'app1',
                'Appendix 1',
                new ArraySequence([
                    new Section(
                        'Appendix 1 title',
                        'app1-1',
                        [new Paragraph('Appendix 1 text')]
                    ),
                ]),
                '10.7554/eLife.09560.app1'
            ),
        ]);

        $article = $this->builder->__invoke();

        $this->assertEquals($appendices, $article->getAppendices());
    }

    /**
     * @test
     */
    public function it_may_have_references()
    {
        $references = new ArraySequence([
            new BookReference(
                'ref1',
                new ReferenceDate(2000),
                null,
                [
                    new PersonAuthor(new PersonDetails(
                        'preferred name',
                        'index name'
                    )),
                ],
                false,
                'book title',
                new Place(null, null, ['publisher'])
            ),
        ]);

        $article = $this->builder->__invoke();

        $this->assertEquals($references, $article->getReferences());
    }

    /**
     * @test
     */
    public function it_may_have_acknowledgements()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new ArraySequence([new Paragraph('acknowledgements')]), $article->getAcknowledgements());
    }

    /**
     * @test
     */
    public function it_may_have_ethics()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new ArraySequence([new Paragraph('ethics')]), $article->getEthics());
    }

    /**
     * @test
     */
    public function it_may_have_a_decision_letter()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withPromiseOfDecisionLetter(null)->__invoke();

        $this->assertEquals(new ArticleSection(new ArraySequence([new Paragraph('Decision letter')])), $with->getDecisionLetter());
        $this->assertNull($withOut->getDecisionLetter());
    }

    /**
     * @test
     */
    public function it_may_have_a_decision_letter_description()
    {
        $article = $this->builder->__invoke();

        $this->assertEquals(new ArraySequence([new Paragraph('Decision letter description')]), $article->getDecisionLetterDescription());
    }

    /**
     * @test
     */
    public function it_may_have_an_author_response()
    {
        $with = $this->builder->__invoke();
        $withOut = $this->builder->withPromiseOfAuthorResponse(null)->__invoke();

        $this->assertEquals(new ArticleSection(new ArraySequence([new Paragraph('Author response')])), $with->getAuthorResponse());
        $this->assertNull($withOut->getAuthorResponse());
    }
}
