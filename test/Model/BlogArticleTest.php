<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class BlogArticleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertSame('id', $blogArticle->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertSame('title', $blogArticle->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new BlogArticle('id', 'title', new DateTimeImmutable(), 'impact statement',
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );
        $withOut = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $blogArticle = new BlogArticle('id', 'title', $date = new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertEquals($date, $blogArticle->getPublishedDate());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Collection $subjects = null, array $expected)
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')), $subjects
        );

        $this->assertEquals($expected, $blogArticle->getSubjects()->toArray());
    }

    public function subjectsProvider() : array
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);

        $subjects = [
            new Subject('subject1', 'Subject 1', null, $image),
            new Subject('subject2', 'Subject 2', null, $image),
        ];

        return [
            'none' => [
                new ArraySequence([]),
                [],
            ],
            'collection' => [
                new ArraySequence($subjects),
                $subjects,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [
            new Block\Paragraph('foo'),
            new Block\Image(
                new Block\ImageFile(null, null, null, null, [], '', 'http://www.example.com/image.jpg', [], [])
            ),
            new Block\Image(
                new Block\ImageFile('10.1000/182', 'foo', 'bar', 'baz', [new Block\Paragraph('qux')], 'quxx',
                    'http://www.example.com/image.jpg', ['corge'], ['grault'])
            ),
            new Block\YouTube('foo', 300, 200),
        ];

        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null, new ArraySequence($content),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertEquals($content, $blogArticle->getContent()->toArray());
    }
}
