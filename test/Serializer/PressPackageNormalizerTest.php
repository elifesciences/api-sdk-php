<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\PressPackagesClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\MediaContact;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PressPackage;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\PressPackageNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
use function GuzzleHttp\Promise\promise_for;

final class PressPackageNormalizerTest extends ApiTestCase
{
    /** @var PressPackageNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new PressPackageNormalizer(new PressPackagesClient($this->getHttpClient()));
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
    public function it_can_normalize_press_packages($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $pressPackage = Builder::dummy(PressPackage::class);

        return [
            'press package' => [$pressPackage, null, true],
            'press package with format' => [$pressPackage, 'foo', true],
            'non-press package' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_press_packages(PressPackage $pressPackage, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($pressPackage, null, $context));
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
    public function it_can_denormalize_press_packages($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'press package' => [[], PressPackage::class, [], true],
            'non-press package' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_press_packages(
        PressPackage $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, PressPackage::class, null, $context);

        $this->mockSubjectCall(1);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updatedDate = new DateTimeImmutable('now', new DateTimeZone('Z'));
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
            new ArraySequence([new Paragraph('Aims and scope text')]), promise_for($banner), promise_for($thumbnail));

        return [
            'complete' => [
                Builder::for(PressPackage::class)
                    ->withId('id')
                    ->withTitle('title')
                    ->withPublished($date)
                    ->withUpdated($updatedDate)
                    ->withImpactStatement('impact statement')
                    ->withSequenceOfSubjects()
                    ->withSequenceOfContent(new Paragraph('Press package id text'))
                    ->withSequenceOfRelatedContent(
                        Builder::for(ArticlePoA::class)
                            ->withStage(ArticlePoA::STAGE_PREVIEW)
                            ->withPublished(null)
                            ->withVersionDate(null)
                            ->withStatusDate(null)
                            ->withAuthorLine(null)
                            ->withPromiseOfIssue(null)
                            ->withPromiseOfXml(null)
                            ->withSequenceOfReviewers()
                            ->withAbstract(null)
                            ->withSequenceOfEthics()
                            ->withPromiseOfFunding(null)
                            ->withSequenceOfGeneratedDataSets()
                            ->withSequenceOfUsedDataSets()
                            ->withSequenceOfAdditionalFiles()
                            ->__invoke()
                    )
                    ->withSequenceOfMediaContacts(new MediaContact(new PersonDetails('preferred', 'index')))
                    ->withSequenceOfAbout(new Paragraph('Press package id about'))
                    ->__invoke(),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updatedDate->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'impact statement',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Press package id text',
                        ],
                    ],
                    'relatedContent' => [
                        [
                            'id' => '14107',
                            'stage' => 'preview',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.14107',
                            'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                            'volume' => 5,
                            'elocationId' => 'e14107',
                            'status' => 'poa',
                        ],
                    ],
                    'mediaContacts' => [
                        [
                            'name' => [
                                'preferred' => 'preferred',
                                'index' => 'index',
                            ],
                        ],
                    ],
                    'about' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Press package id about',
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall('14107', false, false, 1);
                },
            ],
            'minimum' => [
                Builder::for(PressPackage::class)
                    ->withId('id')
                    ->withTitle('title')
                    ->withPublished($date)
                    ->withImpactStatement(null)
                    ->withSequenceOfSubjects()
                    ->withSequenceOfContent(new Paragraph('Press package id text'))
                    ->withSequenceOfRelatedContent()
                    ->withSequenceOfMediaContacts()
                    ->withSequenceOfAbout()
                    ->__invoke(),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Press package id text',
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall('14107', false, false, 1);
                },
            ],
            'complete snippet' => [
                Builder::for(PressPackage::class)
                    ->withId('id')
                    ->withTitle('title')
                    ->withPublished($date)
                    ->withUpdated($updatedDate)
                    ->withImpactStatement('impact statement')
                    ->withSequenceOfSubjects()
                    ->withSequenceOfContent(new Paragraph('Press package id text'))
                    ->withSequenceOfRelatedContent(
                        Builder::for(ArticlePoA::class)
                            ->withStage(ArticlePoA::STAGE_PREVIEW)
                            ->withPublished(null)
                            ->withVersionDate(null)
                            ->withStatusDate(null)
                            ->withAuthorLine(null)
                            ->withPromiseOfIssue(null)
                            ->withPromiseOfXml(null)
                            ->withSequenceOfReviewers()
                            ->withAbstract(null)
                            ->withSequenceOfEthics()
                            ->withPromiseOfFunding(null)
                            ->withSequenceOfGeneratedDataSets()
                            ->withSequenceOfUsedDataSets()
                            ->withSequenceOfAdditionalFiles()
                            ->__invoke()
                    )
                    ->withSequenceOfMediaContacts(new MediaContact(new PersonDetails('preferred', 'index')))
                    ->withSequenceOfAbout(new Paragraph('Press package id about'))
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updatedDate->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'impact statement',
                ],
                function (ApiTestCase $test) {
                    $test->mockPressPackageCall('id', true);
                    $test->mockArticleCall('14107', false, false, 1);
                },
            ],
            'minimum snippet' => [
                Builder::for(PressPackage::class)
                    ->withId('id')
                    ->withTitle('title')
                    ->withPublished($date)
                    ->withImpactStatement(null)
                    ->withSequenceOfSubjects()
                    ->withSequenceOfContent(new Paragraph('Press package id text'))
                    ->withSequenceOfRelatedContent()
                    ->withSequenceOfMediaContacts()
                    ->withSequenceOfAbout()
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                ],
                function (ApiTestCase $test) {
                    $test->mockPressPackageCall('id', false);
                    $test->mockArticleCall('14107', false, false, 1);
                },
            ],
        ];
    }
}
