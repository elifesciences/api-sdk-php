<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference\BookReference;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class ArticleVoRTest extends ArticleTest
{
    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(),
            1, 'elocationId', null, null, [], rejection_for('No abstract'), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')), 'impact statement',
            new PromiseCollection(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseCollection(rejection_for('No content')), new PromiseCollection(rejection_for('No references')));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(),
            1, 'elocationId', null, null, [], rejection_for('No abstract'), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')), null,
            new PromiseCollection(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseCollection(rejection_for('No content')), new PromiseCollection(rejection_for('No references')));

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_keywords()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(),
            1, 'elocationId', null, null, [], rejection_for('No abstract'), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')), null,
            $keywords = new ArrayCollection(['keyword']), rejection_for('No digest'),
            new PromiseCollection(rejection_for('No content')), new PromiseCollection(rejection_for('No references')));

        $this->assertEquals($keywords, $article->getKeywords());
    }

    /**
     * @test
     */
    public function it_may_have_a_digest()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(),
            1, 'elocationId', null, null, [], rejection_for('No abstract'), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')), null,
            new PromiseCollection(rejection_for('No keywords')),
            promise_for($digest = new ArticleSection(new ArrayCollection([new Paragraph('digest')]))),
            new PromiseCollection(rejection_for('No content')), new PromiseCollection(rejection_for('No references')));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(),
            1, 'elocationId', null, null, [], rejection_for('No abstract'), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')), null,
            new PromiseCollection(rejection_for('No keywords')), promise_for(null),
            new PromiseCollection(rejection_for('No content')), new PromiseCollection(rejection_for('No references')));

        $this->assertEquals($digest, $with->getDigest());
        $this->assertNull($withOut->getDigest());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(),
            1, 'elocationId', null, null, [], rejection_for('No abstract'), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')), null,
            new PromiseCollection(rejection_for('No keywords')), rejection_for('No digest'),
            $content = new ArrayCollection([new Paragraph('content')]),
            new PromiseCollection(rejection_for('No references')));

        $this->assertEquals($content, $article->getContent());
    }

    /**
     * @test
     */
    public function it_may_have_references()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(),
            1, 'elocationId', null, null, [], rejection_for('No abstract'), rejection_for('No issue'),
            rejection_for('No copyright'), new PromiseCollection(rejection_for('No authors')), null,
            new PromiseCollection(rejection_for('No keywords')),
            promise_for($digest = new ArticleSection(new ArrayCollection([new Paragraph('digest')]))),
            new PromiseCollection(rejection_for('No content')), $references = new ArrayCollection([
                new BookReference([new PersonAuthor(new Person('preferred name', 'index name'))], false, 'book title',
                    new Place(null, null, ['publisher'])),
            ]));

        $this->assertEquals($references, $article->getReferences());
    }

    protected function createArticleVersion(
        string $id,
        int $version,
        string $type,
        string $doi,
        string $authorLine,
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
    ) : ArticleVersion {
        return new ArticleVoR($id, $version, $type, $doi, $authorLine, $title, $published, $volume, $elocationId, $pdf,
            $subjects, $researchOrganisms, $abstract, $issue, $copyright, $authors, null,
            new PromiseCollection(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseCollection(rejection_for('No content')), new PromiseCollection(rejection_for('No references')));
    }
}
