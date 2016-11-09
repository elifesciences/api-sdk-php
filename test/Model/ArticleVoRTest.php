<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
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
            new PromiseSequence(rejection_for('No authors')), 'impact statement', rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 2, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')), new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_a_banner()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null,
            promise_for($banner = new Image('', [900 => 'https://placehold.it/900x450'])), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No contents')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, promise_for(null), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($banner, $with->getBanner());
        $this->assertNull($withOut->getBanner());
    }

    /**
     * @test
     */
    public function it_may_have_a_thumbnail()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'),
            $thumbnail = new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No contents')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($thumbnail, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_keywords()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            $keywords = new ArraySequence(['keyword']), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
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
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')),
            promise_for($digest = new ArticleSection(new ArraySequence([new Paragraph('digest')]))),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), promise_for(null),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
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
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            $content = new ArraySequence([new Paragraph('content')]), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($content, $article->getContent());
    }

    /**
     * @test
     */
    public function it_may_have_appendices()
    {
        $appendix = new Appendix(
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
        );

        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')),
            promise_for($digest = new ArticleSection(new ArraySequence([new Paragraph('digest')]))),
            new PromiseSequence(rejection_for('No content')), $appendices = new ArraySequence([$appendix]),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($appendices, $article->getAppendices());
    }

    /**
     * @test
     */
    public function it_may_have_references()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')),
            promise_for($digest = new ArticleSection(new ArraySequence([new Paragraph('digest')]))),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            $references = new ArraySequence([
                new BookReference('ref1', new ReferenceDate(2000),
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
                    new Place(null, null, ['publisher'])),
            ]), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($references, $article->getReferences());
    }

    /**
     * @test
     */
    public function it_may_have_acknowledgements()
    {
        $article = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 2, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')),
            promise_for($digest = new ArticleSection(new ArraySequence([new Paragraph('digest')]))),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), $acknowledgements = new ArraySequence([new Paragraph('acknowledgements')]),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));

        $this->assertEquals($acknowledgements, $article->getAcknowledgements());
    }

    /**
     * @test
     */
    public function it_may_have_a_decision_letter()
    {
        $with = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            promise_for($decisionLetter = new ArticleSection(new ArraySequence([new Paragraph('Decision letter')]))),
            new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
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
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
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
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            promise_for($authorResponse = new ArticleSection(new ArraySequence([new Paragraph('Author response')]))));
        $withOut = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new PromiseSequence(rejection_for('No subjects')), [],
            rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
            new PromiseSequence(rejection_for('No authors')), null, rejection_for('No banner'), null,
            new PromiseSequence(rejection_for('No keywords')), rejection_for('No digest'),
            new PromiseSequence(rejection_for('No content')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No acknowledgements')),
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
            rejection_for('No banner'), null, new PromiseSequence(rejection_for('No keywords')),
            rejection_for('No digest'), new PromiseSequence(rejection_for('No content')),
            new PromiseSequence(rejection_for('No references')), new PromiseSequence(rejection_for('No appendices')),
            new PromiseSequence(rejection_for('No acknowledgements')),
            rejection_for('No decision letter'), new PromiseSequence(rejection_for('No decision letter description')),
            rejection_for('No author response'));
    }
}
