<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\AnnotationDocument;
use PHPUnit\Framework\TestCase;

final class AnnotationDocumentTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_title()
    {
        $document = new AnnotationDocument($title = 'Document title', 'http://www.example.com');

        $this->assertSame($title, $document->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $document = new AnnotationDocument('Document title', $uri = 'http://www.example.com');

        $this->assertSame($uri, $document->getUri());
    }
}
