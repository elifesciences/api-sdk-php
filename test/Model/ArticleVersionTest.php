<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Funder;
use eLife\ApiSdk\Model\Funding;
use eLife\ApiSdk\Model\FundingAward;
use eLife\ApiSdk\Model\HasCiteAs;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasPdf;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSocialImage;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Subject;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

abstract class ArticleVersionTest extends TestCase
{
    /** @var Builder */
    protected $builder;

    /**
     * @test
     */
    final public function it_is_an_article()
    {
        $article = $this->builder
            ->__invoke();

        $this->assertInstanceOf(Article::class, $article);
    }

    /**
     * @test
     */
    final public function it_has_an_identifier()
    {
        $article = $this->builder
            ->withId('14107')
            ->__invoke();

        $this->assertInstanceOf(HasIdentifier::class, $article);
        $this->assertEquals(Identifier::article('14107'), $article->getIdentifier());
    }

    /**
     * @test
     */
    final public function it_has_an_id()
    {
        $article = $this->builder
            ->withId('14107')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $article);
        $this->assertSame('14107', $article->getId());
    }

    /**
     * @test
     */
    final public function it_has_a_stage()
    {
        $article = $this->builder
            ->withStage(ArticleVersion::STAGE_PREVIEW)
            ->__invoke();

        $this->assertSame(ArticleVersion::STAGE_PREVIEW, $article->getStage());
    }

    /**
     * @test
     */
    final public function it_has_a_version()
    {
        $article = $this->builder
            ->withVersion(1)
            ->__invoke();

        $this->assertSame(1, $article->getVersion());
    }

    /**
     * @test
     */
    final public function it_has_a_type()
    {
        $article = $this->builder
            ->withType('research-article')
            ->__invoke();

        $this->assertSame('research-article', $article->getType());
    }

    /**
     * @test
     */
    final public function it_may_be_cited()
    {
        $with = $this->builder
            ->withPublished(new DateTimeImmutable('2016-03-28T00:00:00Z'))
            ->withVolume(5)
            ->withElocationId('e14107')
            ->__invoke();
        $withOut = $this->builder
            ->withPublished(null)
            ->__invoke();

        $this->assertInstanceOf(HasCiteAs::class, $with);
        $this->assertSame('eLife 2016;5:e14107', $with->getCiteAs());
        $this->assertNull($withOut->getCiteAs());
    }

    /**
     * @test
     */
    final public function it_has_a_doi()
    {
        $article = $this->builder
            ->withDoi('10.7554/eLife.14107')
            ->__invoke();

        $this->assertInstanceOf(HasDoi::class, $article);
        $this->assertSame('10.7554/eLife.14107', $article->getDoi());
    }

    /**
     * @test
     */
    final public function it_may_have_an_author_line()
    {
        $with = $this->builder
            ->withAuthorLine('Yongjian Huang et al')
            ->__invoke();
        $withOut = $this->builder
            ->withAuthorLine(null)
            ->__invoke();

        $this->assertSame('Yongjian Huang et al', $with->getAuthorLine());
        $this->assertNull($withOut->getAuthorLine());
    }

    /**
     * @test
     */
    final public function it_may_have_a_title_prefix()
    {
        $with = $this->builder
            ->withTitle('Molecular basis for multimerization in the activation of the epidermal growth factor')
            ->withTitlePrefix('title prefix')
            ->__invoke();
        $withOut = $this->builder
            ->withTitle('Molecular basis for multimerization in the activation of the epidermal growth factor')
            ->withTitlePrefix(null)
            ->__invoke();

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
        $article = $this->builder
            ->withTitle('Molecular basis for multimerization in the activation of the epidermal growth factor')
            ->__invoke();

        $this->assertSame('Molecular basis for multimerization in the activation of the epidermal growth factor', $article->getTitle());
    }

    /**
     * @test
     */
    final public function it_may_be_published()
    {
        $with = $this->builder
            ->withPublished(new DateTimeImmutable('2016-03-28T00:00:00Z'))
            ->__invoke();
        $withOut = $this->builder
            ->withPublished(null)
            ->__invoke();

        $this->assertTrue($with->isPublished());
        $this->assertFalse($withOut->isPublished());
    }

    /**
     * @test
     */
    final public function it_may_have_a_published_date()
    {
        $with = $this->builder
            ->withPublished($date = new DateTimeImmutable('2016-03-28T00:00:00Z'))
            ->__invoke();
        $withOut = $this->builder
            ->withPublished(null)
            ->__invoke();

        $this->assertInstanceOf(HasPublishedDate::class, $with);
        $this->assertEquals($date, $with->getPublishedDate());
        $this->assertNull($withOut->getPublishedDate());
    }

    /**
     * @test
     */
    final public function it_may_have_a_version_date()
    {
        $with = $this->builder
            ->withVersionDate($date = new DateTimeImmutable('2016-03-28T00:00:00Z'))
            ->__invoke();
        $withOut = $this->builder
            ->withVersionDate(null)
            ->__invoke();

        $this->assertEquals($date, $with->getVersionDate());
        $this->assertNull($withOut->getVersionDate());
    }

    /**
     * @test
     */
    final public function it_may_have_a_status_date()
    {
        $with = $this->builder
            ->withStatusDate($date = new DateTimeImmutable('2016-03-28T00:00:00Z'))
            ->__invoke();
        $withOut = $this->builder
            ->withStatusDate(null)
            ->__invoke();

        $this->assertEquals($date, $with->getStatusDate());
        $this->assertNull($withOut->getStatusDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_thumbnail()
    {
        $with = $this->builder
            ->withThumbnail($thumbnail = Builder::for(Image::class)->sample('thumbnail'))
            ->__invoke();
        $withOut = $this->builder
            ->withThumbnail(null)
            ->__invoke();

        $this->assertInstanceOf(HasThumbnail::class, $with);
        $this->assertEquals($thumbnail, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_a_social_image()
    {
        $with = $this->builder
            ->withSocialImage($socialImage = Builder::for(Image::class)->sample('social'))
            ->__invoke();
        $withOut = $this->builder
            ->withSocialImage(null)
            ->__invoke();

        $this->assertInstanceOf(HasSocialImage::class, $with);
        $this->assertEquals($socialImage, $with->getSocialImage());
        $this->assertNull($withOut->getSocialImage());
    }

    /**
     * @test
     */
    final public function it_has_a_volume()
    {
        $article = $this->builder
            ->withVolume(5)
            ->__invoke();

        $this->assertSame(5, $article->getVolume());
    }

    /**
     * @test
     */
    final public function it_has_an_elocation_id()
    {
        $article = $this->builder
            ->withElocationId('e14107')
            ->__invoke();

        $this->assertSame('e14107', $article->getElocationId());
    }

    /**
     * @test
     */
    final public function it_may_have_a_pdf()
    {
        $with = $this->builder
            ->withPdf('http://www.example.com/article14107.pdf')
            ->__invoke();
        $withOut = $this->builder
            ->withPdf(null)
            ->__invoke();

        $this->assertInstanceOf(HasPdf::class, $with);
        $this->assertSame('http://www.example.com/article14107.pdf', $with->getPdf());
        $this->assertNull($withOut->getPdf());
    }

    /**
     * @test
     */
    final public function it_may_have_xml()
    {
        $with = $this->builder
            ->withPromiseOfXml('http://www.example.com/article14107.xml')
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfXml(null)
            ->__invoke();

        $this->assertSame('http://www.example.com/article14107.xml', $with->getXml());
        $this->assertNull($withOut->getXml());
    }

    /**
     * @test
     */
    final public function it_may_have_subjects()
    {
        $subjects = new ArraySequence([Builder::dummy(Subject::class)]);

        $article = $this->builder
            ->withSubjects($subjects)
            ->__invoke();

        $this->assertInstanceOf(HasSubjects::class, $article);
        $this->assertEquals($subjects, $article->getSubjects());
    }

    /**
     * @test
     */
    final public function it_may_have_research_organisms()
    {
        $with = $this->builder
            ->withResearchOrganisms(['research organism'])
            ->__invoke();
        $withOut = $this->builder
            ->withResearchOrganisms([])
            ->__invoke();

        $this->assertSame(['research organism'], $with->getResearchOrganisms());
        $this->assertEmpty($withOut->getResearchOrganisms());
    }

    /**
     * @test
     */
    final public function it_may_have_an_abstract()
    {
        $with = $this->builder
            ->withAbstract($abstract = new ArticleSection(new ArraySequence([new Paragraph('Article 14107 abstract text')])))
            ->__invoke();
        $withOut = $this->builder
            ->withAbstract(null)
            ->__invoke();

        $this->assertEquals($abstract, $with->getAbstract());
        $this->assertNull($withOut->getAbstract());
    }

    /**
     * @test
     */
    final public function it_may_have_an_issue()
    {
        $with = $this->builder
            ->withPromiseOfIssue(1)
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfIssue(null)
            ->__invoke();

        $this->assertEquals(1, $with->getIssue());
        $this->assertNull($withOut->getIssue());
    }

    /**
     * @test
     */
    final public function it_has_a_copyright()
    {
        $article = $this->builder
            ->withPromiseOfCopyright($copyright = new Copyright('CC-BY-4.0', 'Statement', 'Author et al'))
            ->__invoke();

        $this->assertEquals($copyright, $article->getCopyright());
    }

    /**
     * @test
     */
    final public function it_may_have_authors()
    {
        $with = $this->builder
            ->withAuthors($authors = new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))]))
            ->__invoke();
        $withOut = $this->builder
            ->withAuthors(new ArraySequence([]))
            ->__invoke();

        $this->assertEquals($authors, $with->getAuthors());
        $this->assertEmpty($withOut->getAuthors());
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
                        new Funder(new Place(['Funder']), '10.13039/501100001659'),
                        'awardId',
                        new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))]),
                        'awardDoi'
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
    public function it_may_have_data_availability()
    {
        $with = $this->builder
            ->withDataAvailability($availability = new ArraySequence([new Paragraph('availability')]))
            ->__invoke();
        $withOut = $this->builder
            ->withDataAvailability(new EmptySequence())
            ->__invoke();

        $this->assertEquals($availability, $with->getDataAvailability());
        $this->assertEmpty($withOut->getDataAvailability());
    }

    /**
     * @test
     */
    public function it_may_have_generated_data_sets()
    {
        $article = $this->builder
            ->withGeneratedDataSets($dataSets = new ArraySequence([Builder::for(DataSet::class)->__invoke()]))
            ->__invoke();

        $this->assertEquals($dataSets, $article->getGeneratedDataSets());
    }

    /**
     * @test
     */
    public function it_may_have_used_data_sets()
    {
        $article = $this->builder
            ->withUsedDataSets($dataSets = new ArraySequence([Builder::for(DataSet::class)->__invoke()]))
            ->__invoke();

        $this->assertEquals($dataSets, $article->getUsedDataSets());
    }

    /**
     * @test
     */
    public function it_may_have_additional_files()
    {
        $article = $this->builder
            ->withAdditionalFiles($files = new ArraySequence([new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'https://placehold.it/900x450', 'image.jpeg'))]))
            ->__invoke();

        $this->assertEquals($files, $article->getAdditionalFiles());
    }
}
