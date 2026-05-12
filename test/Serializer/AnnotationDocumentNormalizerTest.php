<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AnnotationDocument;
use eLife\ApiSdk\Serializer\AnnotationDocumentNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class AnnotationDocumentNormalizerTest extends TestCase
{
    /** @var AnnotationDocumentNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new AnnotationDocumentNormalizer();
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_docuemnts($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $document = new AnnotationDocument('Document title', 'http://www.example.com');

        return [
            'document' => [$document, null, true],
            'document with format' => [$document, 'foo', true],
            'non-document' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_documents(AnnotationDocument $document, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($document));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_documents($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'document' => [[], AnnotationDocument::class, [], true],
            'non-document' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_documents(AnnotationDocument $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, AnnotationDocument::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
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
