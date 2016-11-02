<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\SearchSubjects;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\SearchSubjectsNormalizer;
use stdClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class SearchSubjectsNormalizerTest extends ApiTestCase
{
    /** @var SearchSubjectsNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new SearchSubjectsNormalizer();
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
    public function it_can_normalize_search_subjects($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $searchSubjects = new SearchSubjects(
            [
                Builder::for(Subject::class)->sample('biophysics-structural-biology'),
                Builder::for(Subject::class)->sample('genomics-evolutionary-biology'),
            ],
            [10, 20]
        );

        return [
            'subject' => [$searchSubjects, null, true],
            'subject with format' => [$searchSubjects, 'foo', true],
            'non-subject' => [new stdClass(), null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_search_subjects(SearchSubjects $searchSubjects, array $context, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($searchSubjects, null, $context));
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
            'subject' => [[], SearchSubjects::class, [], true],
            'non-subject' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_subjects(SearchSubjects $expected, array $context, array $json)
    {
        $this->mockSubjectCall('biophysics-structural-biology');
        $this->mockSubjectCall('genomics-evolutionary-biology');

        $actual = $this->normalizer->denormalize($json, SearchSubjects::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new SearchSubjects(
                    [
                        Builder::for(Subject::class)->sample('biophysics-structural-biology'),
                        Builder::for(Subject::class)->sample('genomics-evolutionary-biology'),
                    ],
                    [10, 20]
                ),
                [],
                [
                    [
                        'id' => 'biophysics-structural-biology',
                        'name' => 'Biophysics and Structural Biology',
                        'results' => 10,
                    ],
                    [
                        'id' => 'genomics-evolutionary-biology',
                        'name' => 'Genomics and Evolutionary Biology',
                        'results' => 20,
                    ],
                ],
            ],
        ];
    }
}
