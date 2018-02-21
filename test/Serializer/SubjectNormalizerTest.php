<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class SubjectNormalizerTest extends ApiTestCase
{
    /** @var SubjectNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new SubjectNormalizer(new SubjectsClient($this->getHttpClient()));
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
    public function it_can_normalize_subjects($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $subject = new Subject('id', 'name', promise_for(null), new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')), promise_for($banner), promise_for($thumbnail));

        return [
            'subject' => [$subject, null, true],
            'subject with format' => [$subject, 'foo', true],
            'non-subject' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_subjects(Subject $subject, array $context, array $expected)
    {
        if (!empty($context['snippet'])) {
            $this->mockSubjectCall('subject1');
        }

        $this->assertSame($expected, $this->normalizer->normalize($subject, null, $context));
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
    public function it_can_denormalize_subjects($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'subject' => [[], Subject::class, [], true],
            'non-subject' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_subjects(Subject $expected, array $context, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Subject::class, null, $context);

        if (!empty($context['snippet'])) {
            $this->mockSubjectCall('subject1');
        }

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');

        return [
            'complete' => [
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new ArraySequence([new Paragraph('Subject subject1 aims and scope')]), promise_for($banner), promise_for($thumbnail)),
                [],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                    'image' => [
                        'banner' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                                'filename' => 'banner.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 900,
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                    'impactStatement' => 'Subject subject1 impact statement',
                    'aimsAndScope' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Subject subject1 aims and scope',
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Subject('subject1', 'Subject 1 name', promise_for(null), new EmptySequence(),
                    promise_for($banner), promise_for($thumbnail)),
                [],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                    'image' => [
                        'banner' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                                'filename' => 'banner.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 900,
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                ],
            ],
            'snippet' => [
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($banner), promise_for($thumbnail)),
                ['snippet' => true],
                [
                    'id' => 'subject1',
                    'name' => 'Subject 1 name',
                ],
            ],
        ];
    }
}
