<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
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
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), 'impact statement', null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_an_image()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null,
            $image = new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No contents')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($image, $with->getImage());
        $this->assertNull($withOut->getImage());
    }

    /**
     * @test
     */
    public function it_may_have_keywords()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            $keywords = new ArraySequence(['keyword']), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($keywords, $article->getKeywords());
    }

    /**
     * @test
     */
    public function it_may_have_a_digest()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')),
            promise_for($digest = new ArticleSection(new ArraySequence([new Paragraph('digest')]))),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), promise_for(null),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($digest, $with->getDigest());
        $this->assertNull($withOut->getDigest());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            $content = new ArraySequence([new Paragraph('content')]),
            new PromiseSequence(rejection_for('No references')), rejection_for('No decision letter'),
            new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($content, $article->getContent());
    }

    /**
     * @test
     */
    public function it_may_have_references()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')),
            promise_for($digest = new ArticleSection(new ArraySequence([new Paragraph('digest')]))),
            new PromiseSequence(rejection_for('No content')), $references = new ArraySequence([
                new BookReference(new ReferenceDate(2000),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'book title',
                    new Place(null, null, ['publisher'])),
            ]), rejection_for('No decision letter'),
            new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($references, $article->getReferences());
    }

    /**
     * @test
     */
    public function it_may_have_a_decision_letter()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            promise_for($decisionLetter = new ArticleSection(new ArraySequence([new Paragraph('Decision letter')]))),
            new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            promise_for(null), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($decisionLetter, $with->getDecisionLetter());
        $this->assertNull($withOut->getDecisionLetter());
    }

    /**
     * @test
     */
    public function it_may_have_a_decision_letter_description()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'),
            $decisionLetterDescription = new ArraySequence([new Paragraph('Decision letter description')]),
            rejection_for('No author response'));

        $this->assertEquals($decisionLetterDescription, $article->getDecisionLetterDescription());
    }

    /**
     * @test
     */
    public function it_may_have_an_author_response()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            promise_for($authorResponse = new ArticleSection(new ArraySequence([new Paragraph('Author response')]))));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            promise_for(null));

        $this->assertEquals($authorResponse, $with->getAuthorResponse());
        $this->assertNull($withOut->getAuthorResponse());
    }

    protected function createArticleVersion(
        string $id,
        int $version,
        string $type,
        string $doi,
        string $authorLine,
        string $titlePrefix = null,
        string $title,
        DateTimeImmutable $published,
        DateTimeImmutable $statusDate,
        int $volume,
        string $elocationId,
        string $pdf = null,
        Sequence $subjects,
        array $researchOrganisms,
        PromiseInterface $abstract,
        PromiseInterface $issue,
        PromiseInterface $copyright,
        Sequence $authors
    ) : ArticleVersion {
        return new ArticleVoR($id, $version, $type, $doi, $authorLine, $titlePrefix, $title, $published, $statusDate,
            $volume, $elocationId, $pdf, $subjects, $researchOrganisms, $abstract, $issue, $copyright, $authors, null,
            null, new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No references')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
    }
}
