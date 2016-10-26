<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
//use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticlePoA;
//use eLife\ApiSdk\Model\ArticleSection;
//use eLife\ApiSdk\Model\Block\Paragraph;
//use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\CollectionNormalizer;
//use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class CollectionNormalizerTest extends ApiTestCase
{
    /** @var CollectionNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new CollectionNormalizer(new CollectionsClient($this->getHttpClient()));
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
    public function it_can_normalize_collections($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $collection = Builder::for(Collection::class)->__invoke();

        return [
            'collection' => [$collection, null, true],
            'collection with format' => [$collection, 'foo', true],
            'non-collection' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalizes_collections(Collection $collection, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($collection, null, $context));
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalizes_collections(
        Collection $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Collection::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $this->builder = Builder::for(Collection::class);
        $date = new DateTimeImmutable();
        $banner = Builder::for(Image::class)
            ->sample('banner');
        $thumbnail = Builder::for(Image::class)
            ->sample('thumbnail');
        $subject = Builder::for(Subject::class)
            ->withId('subject1')
            ->withName('Subject 1 name')
            ->withPromiseOfImpactStatement('Subject 1 impact statement')
            ->withPromiseOfBanner($banner)
            ->withPromiseOfThumbnail($thumbnail);
        $subjects = new ArraySequence([
            Builder::for(Subject::class)
                ->withId('epidemiology-global-health')
                ->withName('Epidemiology and Global Health')
                ->__invoke(),
            Builder::for(Subject::class)
                ->withId('microbiology-infectious-disease')
                ->withName('Microbiology and Infectious Disease')
                ->__invoke(),
        ]);

        return [
            'complete' => [
                $this->builder
                    ->withId('1')
                    ->withTitle('Tropical disease')
                    ->withPromiseOfSubTitle('A selection of papers')
                    ->withImpactStatement('eLife has published papers on many...')
                    ->withPublishedDate(new DateTimeImmutable('2015-09-16T11:19:26+00:00'))
                    ->withPromiseOfBanner($banner)
                    ->withThumbnail($thumbnail)
                    ->withSubjects($subjects)
                    ->withSelectedCurator(
                        $selectedCurator = Builder::for(Person::class)
                            ->withId('pjha')
                            ->withType('senior-editor')
                            ->withDetails(new PersonDetails(
                                'Prabhat Jha',
                                'Jha, Prabhat'
                            ))
                            ->__invoke()
                    )
                    ->withSelectedCuratorEtAl(true)
                    ->withCurators(new ArraySequence([
                        Builder::for(Person::class)
                            ->withId('bcooper')
                            ->withType('reviewing-editor')
                            ->withDetails(new PersonDetails(
                                'Ben Cooper',
                                'Cooper, Ben'
                            ))
                            ->__invoke(),
                        $selectedCurator
                    ]))
                    ->withContent(new ArraySequence([
                        Builder::for(ArticleVoR::class)
                            ->sample('homo-naledi'),
                        Builder::for(BlogArticle::class)
                            ->sample('slime'),
                        Builder::for(Interview::class)
                            ->sample('controlling-traffic'),
                    ]))
                    ->withRelatedContent(new ArraySequence([
                        Builder::for(ArticlePoa::class)
                            ->sample('growth-factor'),
                    ]))
                    ->withPodcastEpisodes(new ArraySequence([
                        Builder::for(PodcastEpisode::class)
                            ->sample('29')
                    ]))
                    ->__invoke(),
                ['complete' => true],
array(
  'id' => '1',
  'title' => 'Tropical disease',
  'subTitle' => 'A selection of papers',
  'impactStatement' => 'eLife has published papers on many...',
  'updated' => '2015-09-16T11:19:26+00:00',
  'image' => array(
    'banner' => array(
      'alt' => '',
      'sizes' => array(
        '2:1' => array(
          900 => 'https://placehold.it/900x450',
          1800 => 'https://placehold.it/1800x900',
        ),
      ),
    ),
    'thumbnail' => array(
      'alt' => '',
      'sizes' => array(
        '16:9' => array(
          250 => 'https://placehold.it/250x141',
          500 => 'https://placehold.it/500x281',
        ),
        '1:1' => array(
          70 => 'https://placehold.it/70x70',
          140 => 'https://placehold.it/140x140',
        ),
      ),
    ),
  ),
  'subjects' => array(
    0 => array(
      'id' => 'epidemiology-global-health',
      'name' => 'Epidemiology and Global Health',
    ),
    1 => array(
      'id' => 'microbiology-infectious-disease',
      'name' => 'Microbiology and Infectious Disease',
    ),
  ),
  'selectedCurator' => array(
    'id' => 'pjha',
    'type' => 'senior-editor',
    'name' => array(
      'preferred' => 'Prabhat Jha',
      'index' => 'Jha, Prabhat',
    ),
    'etAl' => true,
  ),
  'curators' => array(
    0 => array(
      'id' => 'bcooper',
      'type' => 'reviewing-editor',
      'name' => array(
        'preferred' => 'Ben Cooper',
        'index' => 'Cooper, Ben',
      ),
    ),
    1 => array(
      'id' => 'pjha',
      'type' => 'senior-editor',
      'name' => array(
        'preferred' => 'Prabhat Jha',
        'index' => 'Jha, Prabhat',
      ),
    ),
  ),
  'content' => array(
    0 => array(
      'type' => 'research-article',
      'status' => 'vor',
      'id' => '09560',
      'version' => 1,
      'doi' => '10.7554/eLife.09560',
      'authorLine' => 'Lee R Berger et al',
      'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
      'published' => '2015-09-10T00:00:00+00:00',
      'statusDate' => '2015-09-10T00:00:00+00:00',
      'volume' => 4,
      'elocationId' => 'e09560',
      'pdf' => 'https://elifesciences.org/content/4/e09560.pdf',
      'subjects' => array(
        0 => array(
          'id' => 'genomics-evolutionary-biology',
          'name' => 'Genomics and Evolutionary Biology',
        ),
      ),
      'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
      'image' => array(
        'thumbnail' => array(
          'alt' => '',
          'sizes' => array(
            '16:9' => array(
              250 => 'https://placehold.it/250x141',
              500 => 'https://placehold.it/500x281',
            ),
            '1:1' => array(
              70 => 'https://placehold.it/70x70',
              140 => 'https://placehold.it/140x140',
            ),
          ),
        ),
      ),
    ),
    1 => array(
      'type' => 'blog-article',
      'id' => '1',
      'title' => 'Media coverage: Slime can see',
      'impactStatement' => 'In their research paper – Cyanobacteria use micro-optics to sense light direction – Schuergers et al. reveal how bacterial cells act as the equivalent of a microscopic eyeball or the world’s oldest and smallest camera eye, allowing them to ‘see’.',
      'published' => '2016-07-08T08:33:25+00:00',
      'subjects' => array(
        0 => array(
          'id' => 'biophysics-structural-biology',
          'name' => 'Biophysics and Structural Biology',
        ),
      ),
    ),
    2 => array(
      'type' => 'interview',
      'id' => '1',
      'interviewee' => array(
        'name' => array(
          'preferred' => 'Ramanath Hegde',
          'index' => 'Hegde, Ramanath',
        ),
      ),
      'title' => 'Controlling traffic',
      'impactStatement' => 'Ramanath Hegde is a Postdoctoral Fellow at the Institute of Protein Biochemistry in Naples, Italy, where he investigates ways of preventing cells from destroying mutant proteins.',
      'published' => '2016-01-29T16:22:28+00:00',
    ),
  ),
  'relatedContent' => array(
    0 => array(
      'type' => 'research-article',
      'status' => 'poa',
      'id' => '14107',
      'version' => 1,
      'doi' => '10.7554/eLife.14107',
      'authorLine' => 'Yongjian Huang et al',
      'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
      'published' => '2016-03-28T00:00:00+00:00',
      'statusDate' => '2016-03-28T00:00:00+00:00',
      'volume' => 5,
      'elocationId' => 'e14107',
    ),
  ),
  'podcastEpisodes' => array(
    0 => array(
      'number' => 29,
      'title' => 'April/May 2016',
      'published' => '2016-05-27T13:19:42+00:00',
      'image' => array(
        'thumbnail' => array(
          'alt' => '',
          'sizes' => array(
            '16:9' => array(
              250 => 'https://placehold.it/250x141',
              500 => 'https://placehold.it/500x281',
            ),
            '1:1' => array(
              70 => 'https://placehold.it/70x70',
              140 => 'https://placehold.it/140x140',
            ),
          ),
        ),
      ),
      'sources' => array(
        0 => array(
          'mediaType' => 'audio/mpeg',
          'uri' => 'https://nakeddiscovery.com/scripts/mp3s/audio/eLife_Podcast_16.05.mp3',
        ),
      ),
    ),
  ),
),
            ],
        ];
    }
}
