<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Funder;
use eLife\ApiSdk\Model\Funding;
use eLife\ApiSdk\Model\FundingAward;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference\BookReference;
use test\eLife\ApiSdk\Builder;

final class ArticleVoRTest extends ArticleTest
{
    public function setUp()
    {
        $this->builder = Builder::for(ArticleVoR::class);
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

        $this->assertSame('A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_a_banner()
    {
        $with = $this->builder
            ->withPromiseOfBanner($banner = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900'])]))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfBanner(null)
            ->__invoke();

        $this->assertEquals($banner, $with->getBanner());
        $this->assertNull($withOut->getBanner());
    }

    /**
     * @test
     */
    public function it_may_have_a_thumbnail()
    {
        $with = $this->builder
            ->withThumbnail($thumbnail = new Image('', [new ImageSize('16:9', [250 => 'https://placehold.it/250x141', 500 => 'https://placehold.it/500x281']), new ImageSize('1:1', ['70' => 'https://placehold.it/70x70', '140' => 'https://placehold.it/140x140'])]))
            ->__invoke();
        $withOut = $this->builder
            ->withThumbnail(null)
            ->__invoke();

        $this->assertEquals($thumbnail, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
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
            ->withContent($content = new ArraySequence([new Section('Article 09560 section title', 'article09560section', [new Paragraph('Article 09560 text')])]))
            ->__invoke();

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
                        [new Paragraph('Appendix 1 text')]
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
                'book title',
                new Place(null, null, ['publisher'])
            ),
        ]);

        $article = $this->builder
            ->withReferences($references)
            ->__invoke();

        $this->assertEquals($references, $article->getReferences());
    }

    /**
     * @test
     */
    public function it_may_have_additional_files()
    {
        $article = $this->builder
            ->withAdditionalFiles(new ArraySequence([new File(null, null, null, null, [], 'image/jpeg', 'https://placehold.it/900x450', 'image.jpeg')]))
            ->__invoke();

        $this->assertEquals($files = new ArraySequence([new File(null, null, null, null, [], 'image/jpeg', 'https://placehold.it/900x450', 'image.jpeg')]), $article->getAdditionalFiles());
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
    public function it_may_have_ethics()
    {
        $article = $this->builder
            ->withEthics($ethics = new ArraySequence([new Paragraph('ethics')]))
            ->__invoke();

        $this->assertEquals($ethics, $article->getEthics());
    }

    /**
     * @test
     */
    public function it_may_have_funding()
    {
        $with = $this->builder
            ->withPromiseOfFunding($funding = new Funding(
                new ArraySequence([
                    new FundingAward(
                        'award',
                        new Funder(new Place(null, null, ['Funder']), '10.13039/501100001659'),
                        'awardId',
                        new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))])
                    ),
                ]),
                'Funding statement'
            ))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfFunding(null)
            ->__invoke();

        $this->assertEquals($funding, $with->getFunding());
        $this->assertNull($withOut->getFunding());
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
}
