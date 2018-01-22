<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AnnotationDocument;
use eLife\ApiSdk\Serializer\AnnotationDocumentNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class AnnotationDocumentNormalizerTest extends TestCase
{
    /** @var AnnotationDocumentNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new AnnotationDocumentNormalizer();
    }

    /**
     * @test
     */
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canNormalizeProvider
     */
    public function it_can_normalize_docuemnts($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $document = new AnnotationDocument('Document title', 'http://www.example.com');

        return [
            'document' => [$document, null, true],
            'document with format' => [$document, 'foo', true],
            'non-document' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_documents(AnnotationDocument $document, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($document));
    }

    /**
     * @test
     */
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canDenormalizeProvider
     */
    public function it_can_denormalize_documents($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'document' => [[], AnnotationDocument::class, [], true],
            'non-document' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_documents(AnnotationDocument $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, AnnotationDocument::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        return [
            'standard' => [
                new AnnotationDocument('Document title', 'http://www.example.com'),
                [
                    'title' => 'Document title',
                    'uri' => 'http://www.example.com',
                ],
            ],
        ];
    }
}
