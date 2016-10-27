<?php

namespace test\eLife\ApiSdk;

use BadMethodCallException;
use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Subject;
use InvalidArgumentException;
use LogicException;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class Builder
{
    private $model;
    private $testData;
    private static $defaults;
    private static $sampleRecipes;

    private function defaultTestData()
    {
        if (self::$defaults === null) {
            self::$defaults = [
                BlogArticle::class => function () {
                    return [
                        'id' => '359325',
                        'title' => 'Media coverage: Slime can see',
                        'published' => new DateTimeImmutable(),
                        'impactStatement' => null,
                        'content' => new ArraySequence([
                            new Paragraph(''),
                        ]),
                        'subjects' => new ArraySequence([]),
                    ];
                },
                Collection::class => function () {
                    return [
                        'id' => 'tropical-disease',
                        'title' => 'Tropical disease',
                        'subTitle' => promise_for(null),
                        'impactStatement' => null,
                        'publishedDate' => new DateTimeImmutable(),
                        'banner' => promise_for(self::for(Image::class)->sample('banner')),
                        'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                        'subjects' => new ArraySequence([]),
                        'selectedCurator' => self::dummy(Person::class),
                        'selectedCuratorEtAl' => false,
                        'curators' => new ArraySequence([
                            self::dummy(Person::class),
                        ]),
                        'content' => new ArraySequence(),
                        'relatedContent' => new ArraySequence(),
                        'podcastEpisodes' => new ArraySequence(),
                    ];
                },
                Image::class => function () {
                    return [
                        'altText' => '',
                        'sizes' => [],
                    ];
                },
                Interview::class => function () {
                    return [
                        'id' => '1',
                        'interviewee' => new Interviewee(
                            new PersonDetails('Ramanath Hegde', 'Hegde, Ramanath'),
                            new ArraySequence([])
                        ),
                        'title' => 'Controlling traffic',
                        'published' => new DateTimeImmutable(),
                        'impactStatement' => null,
                        'content' => $this->rejectSequence(),
                    ];
                },
                Subject::class => function () {
                    return [
                        'id' => 'subject1',
                        'name' => 'Subject 1',
                        'impactStatement' => promise_for('Impact statement'),
                        'banner' => promise_for(self::for(Image::class)->sample('banner')),
                        'thumbnail' => promise_for(self::for(Image::class)->sample('thumbnail')),
                    ];
                },
                Person::class => function () {
                    return [
                        'id' => 'jqpublic',
                        'details' => new PersonDetails('preferred name', 'index name'),
                        'type' => 'senior-editor',
                        'image' => null,
                        'research' => promise_for(null),
                        'profile' => new ArraySequence(),
                        'competingInterests' => promise_for(null),
                    ];
                },
                PodcastEpisode::class => function () {
                    return [
                        'number' => 4,
                        'title' => 'September 2013',
                        'impactStatement' => null,
                        'published' => new DateTimeImmutable(),
                        'banner' => rejection_for('No banner'),
                        'thumbnail' => new Image('', [900 => 'https://placehold.it/900x450']),
                        'sources' => [
                            new PodcastEpisodeSource(
                                'audio/mpeg',
                                'http://example.com/podcast.mp3'
                            ),
                        ],
                        'subjects' => new ArraySequence(),
                        'chapters' => new PromiseSequence(rejection_for('no chapters')),
                    ];
                },
                ArticlePoA::class => $articlePoA = function () {
                    return [
                        'id' => '14107',
                        'type' => 'research-article',
                        'version' => 1,
                        'doi' => '10.7554/eLife.14107',
                        'authorLine' => 'Yongjian Huang et al',
                        'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                        'titlePrefix' => null,
                        'published' => new DateTimeImmutable('2016-03-28T00:00:00+00:00'),
                        'statusDate' => new DateTimeImmutable('2016-03-28T00:00:00+00:00'),
                        'volume' => 5,
                        'elocationId' => 'e14107',
                        'pdf' => null,
                        'subjects' => new ArraySequence(),
                        'researchOrganisms' => [],
                        'abstract' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 14107 abstract text')]))),
                        'issue' => promise_for(1),
                        'copyright' => promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                        'authors' => new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))]),
                    ];
                },
                ArticleVoR::class => function () use ($articlePoA) {
                    return array_merge(
                        $articlePoA(),
                        [
                            'id' => '09560',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.09560',
                            'authorLine' => 'Lee R Berger et al',
                            'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                            'titlePrefix' => null,
                            'published' => new DateTimeImmutable('2015-09-10T00:00:00Z'),
                            'statusDate' => new DateTimeImmutable('2015-09-10T00:00:00Z'),
                            'volume' => 4,
                            'elocationId' => 'e09560',
                            'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                            'banner' => promise_for(self::for(Image::class)->sample('banner')),
                            'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                            'keywords' => new ArraySequence(['Article 09560 keyword']),
                            'digest' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 digest')]), '10.7554/eLife.09560digest')),
                            'content' => new ArraySequence([new Paragraph('content')]),
                            'references' => $references = new ArraySequence([
                                new BookReference(
                                    new ReferenceDate(2000),
                                    [
                                        new PersonAuthor(new PersonDetails(
                                            'preferred name',
                                            'index name'
                                        )),
                                    ],
                                    false,
                                    'book title',
                                    new Place(null, null, ['publisher'])
                                ),
                            ]),
                            'decisionLetter' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Decision letter')]))),
                            'decisionLetterDescription' => new ArraySequence([new Paragraph('Decision letter description')]),
                            'authorResponse' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Author response')]))),
                        ]
                    );
                },
            ];
        }

        return self::$defaults;
    }

    private function sampleRecipes()
    {
        if (self::$sampleRecipes === null) {
            self::$sampleRecipes = [
                Image::class => [
                    'banner' => function () {
                        return new Image(
                            '',
                            [new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900'])]
                        );
                    },
                    'thumbnail' => function () {
                        return new Image('', [
                            new ImageSize('16:9', [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ]),
                            new ImageSize('1:1', [
                                '70' => 'https://placehold.it/70x70',
                                '140' => 'https://placehold.it/140x140',
                            ]),
                        ]);
                    },
                ],
                ArticlePoA::class => [
                    'growth-factor' => function ($builder) {
                        return $builder
                            ->withId('14107')
                            ->withVersion(1)
                            ->withDoi('10.7554/eLife.14107')
                            ->withAuthorLine('Yongjian Huang et al')
                            ->withTitle('Molecular basis for multimerization in the activation of the epidermal growth factor')
                            ->withPublished(new DateTimeImmutable('2016-03-28T00:00:00+00:00'))
                            ->withStatusDate(new DateTimeImmutable('2016-03-28T00:00:00+00:00'))
                            ->withVolume(5)
                            ->withElocationId('e14107')
                            ->withSubjects(new ArraySequence([]));
                    },
                    '1' => function ($builder) {
                        return $builder
                            ->withId('1')
                            ->withVersion(1)
                            ->withDoi('10.7554/eLife.1')
                            ->withAuthorLine('Author et al')
                            ->withTitle('Article 1 title')
                            ->withTitlePrefix('Article 1 title prefix')
                            ->withPublished(new DateTimeImmutable('2000-01-01T00:00:00+00:00'))
                            ->withStatusDate(new DateTimeImmutable('1999-12-31T00:00:00+00:00'))
                            ->withVolume(1)
                            ->withElocationId('e1')
                            ->withPdf('http://www.example.com/')
                            ->withSubjects(new ArraySequence([
                                self::for(Subject::class)->sample('1'),
                            ]))
                            ->withResearchOrganisms([
                                'Article 1 research organism',
                            ])
                            ->withPromiseOfAbstract(new ArticleSection(new ArraySequence([new Paragraph('Article 1 abstract text')])));
                    },
                ],
                ArticleVoR::class => [
                    'homo-naledi' => function ($builder) {
                        return $builder
                            ->withId('09560')
                            ->withVersion(1)
                            ->withDoi('10.7554/eLife.09560')
                            ->withAuthorLine('Lee R Berger et al')
                            ->withTitle('<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa')
                            ->withPublished(new DateTimeImmutable('2015-09-10T00:00:00Z'))
                            ->withStatusDate(new DateTimeImmutable('2015-09-10T00:00:00Z'))
                            ->withVolume(4)
                            ->withElocationId('e09560')
                            ->withPdf('https://elifesciences.org/content/4/e09560.pdf')
                            ->withSubjects(new ArraySequence([
                                self::for(Subject::class)->sample('genomics-evolutionary-biology'),
                            ]))
                            ->withPromiseOfAbstract(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 abstract text')]), '10.7554/eLife.09560abstract'))
                            ->withImpactStatement('A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.')
                            ->withThumbnail(self::for(Image::class)->sample('thumbnail'))
                            ->withContent(new ArraySequence([new Section('Article 09560 section title', 'article09560section', [new Paragraph('Article 09560 text')])]))
                            ->withDecisionLetter(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 decision letter text')]), '10.7554/eLife.09560decisionLetter')))
                            ->withDecisionLetterDescription(new ArraySequence([new Paragraph('Article 09560 decision letter description')]))
                            ->withAuthorResponse(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 author response text')]), '10.7554/eLife.09560authorResponse')));
                    },
                ],
                BlogArticle::class => [
                    'slime' => function ($builder) {
                        return $builder
                            ->withId(359325)
                            ->withTitle('Media coverage: Slime can see')
                            ->withImpactStatement('In their research paper – Cyanobacteria use micro-optics to sense light direction – Schuergers et al. reveal how bacterial cells act as the equivalent of a microscopic eyeball or the world’s oldest and smallest camera eye, allowing them to ‘see’.')
                            ->withPublished(new DateTimeImmutable('2016-07-08T08:33:25+00:00'))
                            ->withSubjects(new ArraySequence([
                                self::for(Subject::class)->sample('biophysics-structural-biology'),
                            ]))
                            ->withContent(new ArraySequence([
                                new Paragraph('Blog article 359325 text'),
                            ]));
                    },
                ],
                Interview::class => [
                    'controlling-traffic' => function ($builder) {
                        return $builder
                            ->withId('1')
                            ->withTitle('Controlling traffic')
                            ->withInterviewee(new Interviewee(
                                new PersonDetails('Ramanath Hegde', 'Hegde, Ramanath'),
                                new ArraySequence([
                                    new IntervieweeCvLine('date', 'text'),
                                ])
                            ))
                            ->withImpactStatement('Ramanath Hegde is a Postdoctoral Fellow at the Institute of Protein Biochemistry in Naples, Italy, where he investigates ways of preventing cells from destroying mutant proteins.')
                            ->withPublished(new DateTimeImmutable('2016-01-29T16:22:28+00:00'))
                            ->withContent(new ArraySequence([new Paragraph('Interview 1 text')]));
                    },
                ],
                Person::class => [
                    'bcooper' => function ($builder, $context) {
                        $person = $builder
                            ->withId('bcooper')
                            ->withType('reviewing-editor')
                            ->withDetails(new PersonDetails(
                                'Ben Cooper',
                                'Cooper, Ben'
                            ));

                        if (!$context['snippet']) {
                            $person
                                ->withPromiseOfResearch('')
                                ->withProfile(new ArraySequence([]))
                                ->withPromiseOfCompetingInterests('');
                        }

                        return $person;
                    },
                    'pjha' => function ($builder, $context) {
                        $person = $builder
                            ->withId('pjha')
                            ->withType('senior-editor')
                            ->withDetails(new PersonDetails(
                                'Prabhat Jha',
                                'Jha, Prabhat'
                            ));
                        if (!$context['snippet']) {
                            $person
                                ->withPromiseOfResearch('')
                                ->withProfile(new ArraySequence([]))
                                ->withPromiseOfCompetingInterests('');
                        }

                        return $person;
                    },
                ],
                PodcastEpisode::class => [
                    '29' => function ($builder) {
                        return $builder
                            ->withNumber(29)
                            ->withTitle('April/May 2016')
                            ->withPublished(new DateTimeImmutable('2016-05-27T13:19:42+00:00'))
                            ->withPromiseOfBanner(self::for(Image::class)->sample('banner'))
                            ->withThumbnail(self::for(Image::class)->sample('thumbnail'))
                            ->withSources([
                                new PodcastEpisodeSource(
                                    'audio/mpeg',
                                    'https://nakeddiscovery.com/scripts/mp3s/audio/eLife_Podcast_16.05.mp3'
                                ),
                            ])
                            ->withChapters(new ArraySequence([new PodcastEpisodeChapter(1, 'Chapter title', 0, 'Chapter impact statement', new ArraySequence([
                                self::for(ArticlePoA::class)->sample('1'),
                            ]))]));
                    },
                ],
                Subject::class => [
                    '1' => function ($builder) {
                        return $builder
                            ->withId('1')
                            ->withName('Subject 1 name')
                            ->withPromiseOfImpactStatement('Subject 1 impact statement');
                    },
                    'genomics-evolutionary-biology' => function ($builder) {
                        return $builder
                            ->withId('genomics-evolutionary-biology')
                            ->withName('Genomics and Evolutionary Biology')
                            ->withPromiseOfImpactStatement('Subject genomics-evolutionary-biology impact statement');
                    },
                    'biophysics-structural-biology' => function ($builder) {
                        return $builder
                            ->withId('biophysics-structural-biology')
                            ->withName('Biophysics and Structural Biology')
                            ->withPromiseOfImpactStatement('Subject biophysics-structural-biology impact statement');
                    },
                    'epidemiology-global-health' => function ($builder) {
                        return $builder
                            ->withId('epidemiology-global-health')
                            ->withName('Epidemiology and Global Health')
                            ->withPromiseOfImpactStatement('Subject epidemiology-global-health impact statement');
                    },
                    'microbiology-infectious-disease' => function ($builder) {
                        return $builder
                            ->withId('microbiology-infectious-disease')
                            ->withName('Microbiology and Infectious Disease')
                            ->withPromiseOfImpactStatement('Subject microbiology-infectious-disease impact statement');
                    },
                ],
            ];
        }

        return self::$sampleRecipes;
    }

    public static function for($model) : self
    {
        return (new self())->create($model);
    }

    /**
     * @return object instance of $model
     */
    public static function dummy($model)
    {
        return self::for($model)->__invoke();
    }

    public function create($model) : self
    {
        $this->model = $model;
        $defaults = $this->defaultTestData($model);
        if (!array_key_exists($model, $defaults)) {
            throw new InvalidArgumentException("No defaults available for $model");
        }

        $this->testData = call_user_func($defaults[$model]);

        return $this;
    }

    /**
     * @method with...($value)  e.g. withImpactStatement('a string')
     * @method withPromiseOf...($value)  e.g. withPromiseOfBanner(new Image(...))
     */
    public function __call($name, $args) : self
    {
        if (preg_match('/^withPromiseOf(.*)$/', $name, $matches)) {
            $field = lcfirst($matches[1]);
            $this->ensureExistingField($field);
            $this->ensureSingleArgument($args);
            $this->testData[$field] = promise_for($args[0]);
        } elseif (preg_match('/^with(.*)$/', $name, $matches)) {
            $field = lcfirst($matches[1]);
            $this->ensureExistingField($field);
            $this->ensureSingleArgument($args);
            $this->testData[$field] = $args[0];
        } else {
            throw new BadMethodCallException("Magic method $name is not supported by the Builder class");
        }

        return $this;
    }

    /**
     * @return object instance of $this->model
     */
    public function __invoke()
    {
        $class = new \ReflectionClass($this->model);
        $constructorArgumentNames = array_map(function ($p) {
            return $p->getName();
        }, $class->getConstructor()->getParameters());
        $constructorArguments = [];
        $testDataRemainingToUse = $this->testData;
        foreach ($constructorArgumentNames as $name) {
            $constructorArguments[] = $testDataRemainingToUse[$name];
            unset($testDataRemainingToUse[$name]);
        }
        if ($testDataRemainingToUse) {
            throw new LogicException("Some defaults were specified, but not used by the constructor of $this->model: ".var_export($testDataRemainingToUse, true));
        }
        $instance = $class->newInstanceArgs($constructorArguments);

        return $instance;
    }

    /**
     * @return object instance of $this->model
     */
    public function sample($sampleName, $context = [])
    {
        $samples = $this->sampleRecipes();

        if (!array_key_exists($sampleName, $samples[$this->model])) {
            throw new InvalidArgumentException("Sample $sampleName not found for {$this->model}");
        }
        if (!array_key_exists('snippet', $context)) {
            // what should be the default?
            $context['snippet'] = true;
        }
        $sample = call_user_func(
            $samples[$this->model][$sampleName],
            $this,
            $context
        );
        if ($sample instanceof self) {
            return $sample->__invoke();
        } else {
            return $sample;
        }
    }

    private function ensureExistingField($field)
    {
        $allowedFields = array_keys($this->testData);
        if (!in_array($field, $allowedFields)) {
            throw new BadMethodCallException("Field $field is not allowed for {$this->model}. Allowed fields: ".implode(', ', $allowedFields));
        }
    }

    private function ensureSingleArgument($args)
    {
        if (count($args) > 1) {
            throw new BadMethodCallException('Too many arguments: '.var_export($args, true));
        }
    }

    private function rejectSequence()
    {
        return new PromiseSequence(rejection_for('rejecting this sequence'));
    }
}
