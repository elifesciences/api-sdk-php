<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Annotation;
use eLife\ApiSdk\Model\AnnotationDocument;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Serializer\AnnotationNormalizer;
use function GuzzleHttp\Promise\rejection_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;

final class AnnotationNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var AnnotationNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new AnnotationNormalizer();
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
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
    public function it_can_normalize_annotations($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $annotation = new Annotation('id', 'public', new AnnotationDocument('title', 'http://example.com'), new PromiseSequence(rejection_for('Annotation ancestors should not be unwrapped')), 'Highlighted text', new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Annotation content should not be unwrapped'))
        );

        return [
            'annotation' => [$annotation, null, true],
            'annotation with format' => [$annotation, 'foo', true],
            'non-event' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_annotations(Annotation $annotation, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($annotation, null, $context));
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
    public function it_can_denormalize_events($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'annotation' => [[], Annotation::class, [], true],
            'non-annotation' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_annotations(
        Annotation $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Annotation::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $created = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updated = new DateTimeImmutable('-2 days', new DateTimeZone('Z'));

        return [
            'complete with content and highlight' => [
                new Annotation('id1', 'public', new AnnotationDocument('Document title', 'http://example.com'), new ArraySequence(['id2']), 'Highlighted text', $created, $updated,
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => 'id1',
                    'access' => 'public',
                    'document' => [
                        'title' => 'Document title',
                        'uri' => 'http://example.com',
                    ],
                    'ancestors' => [
                        'id2',
                    ],
                    'highlight' => 'Highlighted text',
                    'created' => $created->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete with content, no highlight' => [
                new Annotation('id1', 'public', new AnnotationDocument('Document title', 'http://example.com'), new ArraySequence(['id2']), null, $created, $updated,
                    new ArraySequence([new Paragraph('text')])),
                [],
                [
                    'id' => 'id1',
                    'access' => 'public',
                    'document' => [
                        'title' => 'Document title',
                        'uri' => 'http://example.com',
                    ],
                    'ancestors' => [
                        'id2',
                    ],
                    'created' => $created->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete with highlight, no content' => [
                new Annotation('id1', 'public', new AnnotationDocument('Document title', 'http://example.com'), new ArraySequence(['id2']), 'Highlighted text', $created, $updated,
                    new EmptySequence()),
                [],
                [
                    'id' => 'id1',
                    'access' => 'public',
                    'document' => [
                        'title' => 'Document title',
                        'uri' => 'http://example.com',
                    ],
                    'ancestors' => [
                        'id2',
                    ],
                    'highlight' => 'Highlighted text',
                    'created' => $created->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                ],
            ],
            'minimum' => [
                new Annotation('id', 'public', new AnnotationDocument('Document title', 'http://example.com'), new EmptySequence(), 'Highlighted text', $created, null,
                    new EmptySequence()),
                [],
                [
                    'id' => 'id',
                    'access' => 'public',
                    'document' => [
                        'title' => 'Document title',
                        'uri' => 'http://example.com',
                    ],
                    'highlight' => 'Highlighted text',
                    'created' => $created->format(ApiSdk::DATE_FORMAT),
                ],
            ],
        ];
    }

    protected function class() : string
    {
        return Annotation::class;
    }

    protected function samples()
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/annotation-list/v1/*.json#items';
    }
}
