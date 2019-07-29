<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Annotation;
use eLife\ApiSdk\Model\AnnotationDocument;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasCreatedDate;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Model;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit_Framework_TestCase;

final class AnnotationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $annotation = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), 'Highlighted text', new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertInstanceOf(Model::class, $annotation);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $annotation = new Annotation($id = 'id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertInstanceOf(HasIdentifier::class, $annotation);
        $this->assertEquals(Identifier::annotation($id), $annotation->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $annotation = new Annotation($id = 'id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertInstanceOf(HasId::class, $annotation);
        $this->assertSame($id, $annotation->getId());
    }

    /**
     * @test
     */
    public function it_has_an_access_level()
    {
        $annotation = new Annotation('id', $access = 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertSame($access, $annotation->getAccess());
    }

    /**
     * @test
     */
    public function it_has_a_document()
    {
        $annotation = new Annotation('id', 'public', $document = new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertSame($document, $annotation->getDocument());
    }

    /**
     * @test
     */
    public function it_may_have_ancestors()
    {
        $ancestors = [
            'id2',
            'id3',
        ];

        $with = new Annotation('id1', 'public', new AnnotationDocument('title', 'http://example.com'), new ArraySequence($ancestors), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );
        $withOut = new Annotation('id1', 'public', new AnnotationDocument('title', 'http://example.com'), new EmptySequence(), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertSame($ancestors, $with->getAncestors()->toArray());
        $this->assertTrue($withOut->getAncestors()->isEmpty());
        $this->assertEmpty($withOut->getAncestors()->toArray());
    }

    /**
     * @test
     */
    public function it_has_a_created_date()
    {
        $annotation = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertInstanceOf(HasCreatedDate::class, $annotation);
        $this->assertSame($date, $annotation->getCreatedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), $date = new DateTimeImmutable('now', new DateTimeZone('Z')),
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );
        $withOut = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($date, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_highlight()
    {
        $with = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), $highlight = 'Highlighted text', new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );
        $withOut = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        $this->assertSame($highlight, $with->getHighlight());
        $this->assertNull($withOut->getHighlight());
    }

    /**
     * @test
     */
    public function it_may_have_content()
    {
        $content = [
            new Block\Paragraph('foo'),
            new Block\YouTube('foo', null, new EmptySequence(), 300, 200),
        ];

        $with = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new ArraySequence($content)
        );
        $withOut = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new EmptySequence()
        );

        $this->assertInstanceOf(HasContent::class, $with);
        $this->assertEquals($content, $with->getContent()->toArray());
        $this->assertTrue($withOut->getContent()->isEmpty());
        $this->assertEmpty($withOut->getContent()->toArray());
    }
}
