<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasCurationLabels;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasReferences;
use eLife\ApiSdk\Model\HasReviewedDate;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\PublicReview;
use eLife\ApiSdk\Model\Reference\BookReference;
use test\eLife\ApiSdk\Builder;

final class ArticleVoRTest extends ArticleVersionTest
{
    public function setUp(): void
    {
        $this->builder = Builder::for(ArticleVoR::class);
    }

    /**
     * @test
     */
    public function it_may_have_a_figures_pdf()
    {
        $with = $this->builder
            ->withFiguresPdf('http://www.example.com/article14107.pdf')
            ->__invoke();
        $withOut = $this->builder
            ->withFiguresPdf(null)
            ->__invoke();

        $this->assertSame('http://www.example.com/article14107.pdf', $with->getFiguresPdf());
        $this->assertNull($withOut->getFiguresPdf());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = $this->builder
            ->withImpactStatement('A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.')
            ->__invoke();
        $withOut = $this->builder
            ->withImpactStatement(null)
            ->__invoke();

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_keywords()
    {
        $article = $this->builder
            ->withKeywords($keywords = new ArraySequence(['keyword']))
            ->__invoke();

        $this->assertEquals($keywords, $article->getKeywords());
    }

    /**
     * @test
     */
    public function it_may_have_a_digest()
    {
        $with = $this->builder
            ->withPromiseOfDigest($digest = new ArticleSection(new ArraySequence([new Paragraph('Article 09560 digest')]), '10.7554/eLife.09560digest'))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfDigest(null)
            ->__invoke();

        $this->assertEquals($digest, $with->getDigest());
        $this->assertNull($withOut->getDigest());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $article = $this->builder
            ->withContent($content = new ArraySequence([new Section('Article 09560 section title', 'article09560section', new ArraySequence([new Paragraph('Article 09560 text')]))]))
            ->__invoke();

        $this->assertInstanceOf(HasContent::class, $article);
        $this->assertEquals($content, $article->getContent());
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
                        new ArraySequence([new Paragraph('Appendix 1 text')])
                    ),
                ]),
                '10.7554/eLife.09560.app1'
            ),
        ]);

        $article = $this->builder
            ->withAppendices($appendices)
            ->__invoke();

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
                new Date(2000),
                null,
                [
                    new PersonAuthor(new PersonDetails(
                        'preferred name',
                        'index name'
                    )),
                ],
                false,
                [],
                false,
                'book title',
                new Place(['publisher'])
            ),
        ]);

        $article = $this->builder
            ->withReferences($references)
            ->__invoke();

        $this->assertInstanceOf(HasReferences::class, $article);
        $this->assertEquals($references, $article->getReferences());
    }

    /**
     * @test
     */
    public function it_may_have_acknowledgements()
    {
        $article = $this->builder
            ->withAcknowledgements($acknowledgments = new ArraySequence([new Paragraph('acknowledgements')]))
            ->__invoke();

        $this->assertEquals($acknowledgments, $article->getAcknowledgements());
    }

    /**
     * @test
     */
    public function it_may_have_an_editor_evaluation()
    {
        $with = $this->builder
            ->withPromiseOfEditorEvaluation($editorEvaluation = new ArticleSection(new ArraySequence([new Paragraph('Editor evaluation')])))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfEditorEvaluation(null)
            ->__invoke();

        $this->assertEquals($editorEvaluation, $with->getEditorEvaluation());
        $this->assertNull($withOut->getEditorEvaluation());
    }

    /**
     * @test
     */
    public function it_may_have_an_editor_evaluation_uri()
    {
        $with = $this->builder
            ->withPromiseOfEditorEvaluationScietyUri($editorEvaluationScietyUri = 'https://editor-evaluation.com')
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfEditorEvaluationScietyUri(null)
            ->__invoke();

        $this->assertEquals($editorEvaluationScietyUri, $with->getEditorEvaluationScietyUri());
        $this->assertNull($withOut->getEditorEvaluationScietyUri());
    }

    /**
     * @test
     */
    public function it_may_have_a_decision_letter()
    {
        $with = $this->builder
            ->withPromiseOfDecisionLetter($decisionLetter = new ArticleSection(new ArraySequence([new Paragraph('Decision letter')])))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfDecisionLetter(null)
            ->__invoke();

        $this->assertEquals($decisionLetter, $with->getDecisionLetter());
        $this->assertNull($withOut->getDecisionLetter());
    }

    /**
     * @test
     */
    public function it_may_have_a_decision_letter_description()
    {
        $article = $this->builder
            ->withDecisionLetterDescription($description = new ArraySequence([new Paragraph('Decision letter description')]))
            ->__invoke();

        $this->assertEquals($description, $article->getDecisionLetterDescription());
    }

    /**
     * @test
     */
    public function it_may_have_an_author_response()
    {
        $with = $this->builder
            ->withPromiseOfAuthorResponse($authorResponse = new ArticleSection(new ArraySequence([new Paragraph('Author response')])))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfAuthorResponse(null)
            ->__invoke();

        $this->assertEquals($authorResponse, $with->getAuthorResponse());
        $this->assertNull($withOut->getAuthorResponse());
    }

    /**
     * @test
     */
    public function it_may_have_a_reviewed_date()
    {
        $with = $this->builder
            ->withReviewedDate($date = new DateTimeImmutable('2022-09-15'))
            ->__invoke();
        $withOut = $this->builder
            ->withReviewedDate(null)
            ->__invoke();

        $this->assertInstanceOf(HasReviewedDate::class, $with);
        $this->assertEquals($date, $with->getReviewedDate());
        $this->assertNull($withOut->getReviewedDate());
    }

    /**
     * @test
     */
    public function it_may_have_curation_labels()
    {
        $with = $this->builder
            ->withCurationLabels(['Landmark', 'Exceptional'])
            ->__invoke();
        $withOut = $this->builder
            ->withCurationLabels([])
            ->__invoke();

        $this->assertInstanceOf(HasCurationLabels::class, $with);
        $this->assertEquals(['Landmark', 'Exceptional'], $with->getCurationLabels());
        $this->assertEmpty($withOut->getCurationLabels());
    }

    /**
     * @test
     */
    public function it_may_be_vor_prc()
    {
        /** @var ArticleVoR $articleVorPRC */
        $articleVorPRC = $this->builder
            ->withElocationId('RP00569')
            ->__invoke();

        $this->assertTrue($articleVorPRC->isReviewedPreprint());

        /** @var ArticleVoR $articleVorTraditional */
        $articleVorTraditional = $this->builder
            ->withElocationId('e00569')
            ->__invoke();

        $this->assertFalse($articleVorTraditional->isReviewedPreprint());
    }

        /**
     * @test
     */
    public function it_may_have_an_elife_assessment()
    {
        $with = $this->builder
            ->withElifeAssessment($elifeAssessment = Builder::dummy(ElifeAssessment::class))
            ->__invoke();
        $withOut = $this->builder
            ->withElifeAssessment(null)
            ->__invoke();

        $this->assertEquals($elifeAssessment, $with->getElifeAssessment());
        $this->assertNull($withOut->getElifeAssessment());
    }

    /**
     * @test
     */
    public function it_may_have_an_elife_assessment_article_section()
    {
        $with = $this->builder
            ->withElifeAssessmentArticleSection($elifeAssessment = new ArticleSection(new ArraySequence([new Paragraph('eLife assessment')])))
            ->__invoke();
        $withOut = $this->builder
            ->withElifeAssessmentArticleSection(null)
            ->__invoke();

        $this->assertEquals($elifeAssessment, $with->getElifeAssessmentArticleSection());
        $this->assertNull($withOut->getElifeAssessmentArticleSection());
    }

    /**
     * @test
     */
    public function it_may_have_an_elife_assessment_uri()
    {
        $with = $this->builder
            ->withElifeAssessmentScietyUri($elifeAssessmentScietyUri = 'https://elife-assessment.com')
            ->__invoke();
        $withOut = $this->builder
            ->withElifeAssessmentScietyUri(null)
            ->__invoke();

        $this->assertEquals($elifeAssessmentScietyUri, $with->getElifeAssessmentScietyUri());
        $this->assertNull($withOut->getElifeAssessmentScietyUri());
    }

    /**
     * @test
     */
    public function it_may_have_recommendations_for_authors()
    {
        $with = $this->builder
            ->withPromiseOfRecommendationsForAuthors($recommendationsForAuthors = new ArticleSection(new ArraySequence([new Paragraph('Recommendations for authors')])))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfRecommendationsForAuthors(null)
            ->__invoke();

        $this->assertEquals($recommendationsForAuthors, $with->getRecommendationsForAuthors());
        $this->assertNull($withOut->getRecommendationsForAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_a_recommendations_for_authors_title()
    {
        $with = $this->builder
            ->withPromiseOfRecommendationsForAuthorsTitle($recommendationsForAuthorsTitle = 'Recommendations for authors')
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfRecommendationsForAuthorsTitle(null)
            ->__invoke();

        $this->assertEquals($recommendationsForAuthorsTitle, $with->getRecommendationsForAuthorsTitle());
        $this->assertNull($withOut->getRecommendationsForAuthorsTitle());
    }

    /**
     * @test
     */
    public function it_may_have_public_reviews()
    {
        $with = $this->builder
            ->withPublicReviews($publicReviews = new ArraySequence([new PublicReview('Public review 1', new ArraySequence([new Paragraph('Public review 1 content')]))]))
            ->__invoke();
        $withOut = $this->builder
            ->withPublicReviews(new EmptySequence())
            ->__invoke();

        $this->assertEquals($publicReviews, $with->getPublicReviews());
        $this->assertEquals(new EmptySequence(), $withOut->getPublicReviews());
    }

    /**
     * @test
     */
    public function it_may_have_version_doi()
    {
        $with = $this->builder
            ->withDoiVersion('10.7554/eLife.09560.1')
            ->__invoke();
        $withOut = $this->builder
            ->withDoiVersion(null)
            ->__invoke();

        $this->assertEquals('10.7554/eLife.09560.1', $with->getDoiVersion());
        $this->assertNull($withOut->getDoiVersion());
    }
}
