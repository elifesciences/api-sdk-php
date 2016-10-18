<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

abstract class ArticleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    final public function it_has_an_id()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')));

        $this->assertSame('id', $article->getId());
    }

    /**
     * @test
     */
    final public function it_has_a_version()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame(1, $article->getVersion());
    }

    /**
     * @test
     */
    final public function it_has_a_type()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame('type', $article->getType());
    }

    /**
     * @test
     */
    final public function it_has_a_doi()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame('doi', $article->getDoi());
    }

    /**
     * @test
     */
    final public function it_has_an_author_line()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame('author line', $article->getAuthorLine());
    }

    /**
     * @test
     */
    final public function it_may_have_a_title_prefix()
    {
        $with = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', 'title prefix', 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));
        $withOut = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame('title prefix', $with->getTitlePrefix());
        $this->assertSame('title prefix: title', $with->getFullTitle());
        $this->assertNull($withOut->getTitlePrefix());
        $this->assertSame('title', $withOut->getFullTitle());
    }

    /**
     * @test
     */
    final public function it_has_a_title()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(),
            1, 'elocationId', null, null, [], rejection_for('No abstract'), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')));

        $this->assertSame('title', $article->getTitle());
    }

    /**
     * @test
     */
    final public function it_has_a_published_date()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            $date = new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertEquals($date, $article->getPublishedDate());
    }

    /**
     * @test
     */
    final public function it_has_a_volume()
    {
        $article = $this->createArticleVersion('id', 2, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame(1, $article->getVolume());
    }

    /**
     * @test
     */
    final public function it_has_an_elocation_id()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame('elocationId', $article->getElocationId());
    }

    /**
     * @test
     */
    final public function it_may_have_a_pdf()
    {
        $with = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', 'http://www.example.com/article.pdf', null, [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));
        $withOut = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame('http://www.example.com/article.pdf', $with->getPdf());
        $this->assertNull($withOut->getPdf());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    final public function it_may_have_subjects(Collection $subjects = null, bool $hasSubjects, array $expected)
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, $subjects, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame($hasSubjects, $article->hasSubjects());
        $this->assertEquals($expected, $article->getSubjects()->toArray());
    }

    final public function subjectsProvider() : array
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);

        $subjects = [
            new Subject('subject1', 'Subject 1', null, $image),
            new Subject('subject2', 'Subject 2', null, $image),
        ];

        return [
            'none' => [
                null,
                false,
                [],
            ],
            'collection' => [
                new ArrayCollection($subjects),
                true,
                $subjects,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_not_unwrap_subjects_when_checking_if_it_has_any()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null,
            new PromiseCollection(rejection_for('Subjects should not be unwrapped')), [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertTrue($article->hasSubjects());
    }

    /**
     * @test
     */
    final public function it_may_have_research_organisms()
    {
        $with = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, ['organism'], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));
        $withOut = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertSame(['organism'], $with->getResearchOrganisms());
        $this->assertEmpty($withOut->getResearchOrganisms());
    }

    /**
     * @test
     */
    final public function it_may_have_an_abstract()
    {
        $with = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [],
            promise_for($abstract = new ArticleSection(new ArrayCollection([new Paragraph('abstract')]))),
            rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseCollection(rejection_for('No authors')));
        $withOut = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], promise_for(null), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')));

        $this->assertEquals($abstract, $with->getAbstract());
        $this->assertNull($withOut->getAbstract());
    }

    /**
     * @test
     */
    final public function it_may_have_an_issue()
    {
        $with = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'), promise_for(3),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')));
        $withOut = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'), promise_for(null),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')));

        $this->assertEquals(3, $with->getIssue());
        $this->assertNull($withOut->getIssue());
    }

    /**
     * @test
     */
    final public function it_has_a_copyright()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'), promise_for(3),
            promise_for($copyright = new Copyright('license', 'statement')),
            new PromiseCollection(rejection_for('No authors')));

        $this->assertEquals($copyright, $article->getCopyright());
    }

    /**
     * @test
     */
    final public function it_has_authors()
    {
        $article = $this->createArticleVersion('id', 1, 'type', 'doi', 'author line', null, 'title',
            new DateTimeImmutable(), 1, 'elocationId', null, null, [], rejection_for('No abstract'), promise_for(3),
            rejection_for('No copyright'),
            $authors = new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]));

        $this->assertEquals($authors, $article->getAuthors());
    }

    abstract protected function createArticleVersion(
        string $id,
        int $version,
        string $type,
        string $doi,
        string $authorLine,
        string $titlePrefix = null,
        string $title,
        DateTimeImmutable $published,
        int $volume,
        string $elocationId,
        string $pdf = null,
        Collection $subjects = null,
        array $researchOrganisms,
        PromiseInterface $abstract,
        PromiseInterface $issue,
        PromiseInterface $copyright,
        Collection $authors
    ) : ArticleVersion;
}
