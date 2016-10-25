<?php

namespace test\eLife\ApiSdk;

use BadMethodCallException;
use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Subject;
use InvalidArgumentException;
use LogicException;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class Builder
{
    private $model;
    private $testData;

    public static function for($model)
    {
        return (new self())->create($model);
    }

    public static function dummy($model)
    {
        return self::for($model)->__invoke();
    }

    public function create($model)
    {
        $this->model = $model;
        $this->testData = call_user_func($this->defaultTestDataFor($model));

        return $this;
    }

    /**
     * @method with...($value)  e.g. withImpactStatement('a string')
     */
    public function __call($name, $args)
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
            throw new LogicException("Some defaults were specified, but not used by the constructor of $this->model: " . var_export($testDataRemainingToUse, true));
        }
        $instance = $class->newInstanceArgs($constructorArguments);

        return $instance;
    }

    public function sample($sampleName)
    {
        $samples = $this->samples();

        $sample = call_user_func(
            $samples[$this->model][$sampleName],
            $this
        );
        if ($sample instanceof self) {
            return $sample->__invoke();
        } else {
            return $sample;
        }
    }

    private function defaultTestDataFor($model)
    {
        // TODO: turn into private field
        $defaults = [
            BlogArticle::class => function () {
                return [
                    'id' => '359325',
                    'title' => 'Media coverage: Slime can see',
                    'published' => new DateTimeImmutable(),
                    'impactStatement' => null,
                    'content' => new PromiseSequence(rejection_for('no content')),
                    'subjects' => new ArraySequence([]),
                ];
            },
            Collection::class => function () {
                return [
                    'id' => 'tropical-disease',
                    'title' => 'Tropical disease',
                    'subTitle' => rejection_for('Tropical disease subtitle'),
                    'impactStatement' => null,
                    'publishedDate' => new DateTimeImmutable(),
                    'banner' => rejection_for('No banner'),
                    'thumbnail' => new Image('', [900 => 'https://placehold.it/900x450']),
                    'subjects' => new ArraySequence([]),
                    'selectedCurator' => self::dummy(Person::class),
                    'selectedCuratorEtAl' => false,
                    'curators' => new PromiseSequence(rejection_for('no curators')),
                    'content' => new PromiseSequence(rejection_for('no content')),
                    'relatedContent' => new PromiseSequence(rejection_for('no related content')),
                    'podcastEpisodes' => new PromiseSequence(rejection_for('no podcast episodes')),
                ];
            },
            Image::class => function () {
                return [
                    'altText' => '',
                    'sizes' => [],
                ];
            },
            Interview::class => function() {
                return [
                    'id' => '1',
                    'interviewee' => new Interviewee(
                        new PersonDetails("Ramanath Hegde", "Hegde, Ramanath"),
                        $this->rejectSequence()
                    ),
                    'title' => 'Controlling traffic',
                    'published' => new DateTimeImmutable(),
                    'impactStatement' => null,
                    'content' => $this->rejectSequence()
                ];
            },
            Subject::class => function () {
                return [
                    'id' => 'subject1',
                    'name' => 'Subject 1',
                    'impactStatement' => rejection_for('No impact statement'),
                    'banner' => rejection_for('No banner'),
                    'thumbnail' => rejection_for('No thumbnail'),
                ];
            },
            Person::class => function () {
                return [
                    'id' => 'jqpublic',
                    'details' => new PersonDetails('preferred name', 'index name'),
                    'type' => 'senior-editor',
                    'image' => null,
                    'research' => rejection_for('Research should not be unwrapped'),
                    'profile' => new PromiseSequence(rejection_for('Profile should not be unwrapped')),
                    'competingInterests' => rejection_for('Competing interests should not be unwrapped'),
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
                    'subjects' => new ArraySequence([]),
                    'chapters' => new PromiseSequence(rejection_for('no chapters')),
                ];
            },
            ArticleVoR::class => function() {
                return [
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
                    'pdf' => 'https://elifesciences.org/content/4/e09560.pdf',
                    'subjects' => new ArraySequence([
                        self::for(Subject::class)->sample('genomics-evolutionary-biology')
                    ]),
                    'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                    'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                    'researchOrganisms' => [],
                    'abstract' => rejection_for('no abstract'),
                    'issue' => rejection_for('no issue'),
                    'copyright' => rejection_for('no copyright'),
                    'authors' => $this->rejectSequence(),
                    'banner' => rejection_for('no banner'),
                    'keywords' => $this->rejectSequence(),
                    'digest' => rejection_for('no banner'),
                    'content' => $this->rejectSequence(),
                    'references' => $this->rejectSequence(),
                    'decisionLetter' => rejection_for('no decision letter'),
                    'decisionLetterDescription' => $this->rejectSequence(),
                    'authorResponse' => rejection_for('no author response'),
                ];
            },
        ];

        if (!array_key_exists($model, $defaults)) {
            throw new InvalidArgumentException("No defaults available for $model");
        }

        return $defaults[$model];
    }

    private function samples()
    {
        // TODO: turn into private field
        return [
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
            ArticleVoR::class => [
                'homo-naledi' => function () {
                    return self::for(ArticleVoR::class)
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
                            self::for(Subject::class)->sample('genomics-evolutionary-biology')
                        ]))
                        ->withImpactStatement('A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.')
                        ->withThumbnail(self::for(Image::class)->sample('thumbnail'));
                },
            ],
            BlogArticle::class => [
                'slime' => function($builder) {
                    return $builder
                        ->withId(1)
                        ->withTitle('Media coverage: Slime can see')
                        ->withImpactStatement('In their research paper – Cyanobacteria use micro-optics to sense light direction – Schuergers et al. reveal how bacterial cells act as the equivalent of a microscopic eyeball or the world’s oldest and smallest camera eye, allowing them to ‘see’.')
                        ->withPublished(new DateTimeImmutable('2016-07-08T08:33:25+00:00'))
                        ->withSubjects(new ArraySequence([
                            self::for(Subject::class)->sample('biophysics-structural-biology')
                        ]));
                },
            ],
            Interview::class => [
                'controlling-traffic' => function($builder) {
                    return $builder
                        ->withId('1')
                        ->withTitle('Controlling traffic')
                        ->withInterviewee(new Interviewee(
                                new PersonDetails("Ramanath Hegde", "Hegde, Ramanath"),
                                $this->rejectSequence()
                        ))
                        ->withImpactStatement("Ramanath Hegde is a Postdoctoral Fellow at the Institute of Protein Biochemistry in Naples, Italy, where he investigates ways of preventing cells from destroying mutant proteins.")
                        ->withPublished(new DateTimeImmutable("2016-01-29T16:22:28+00:00"))
                                                ;
                },
            ],
            Subject::class => [
                'genomics-evolutionary-biology' => function() {
                    // TODO: maybe pass in a ready Builder::for(SomeModel::class)?
                    return self::for(Subject::class)
                        ->withId('genomics-evolutionary-biology')
                        ->withName('Genomics and Evolutionary Biology');
                },
                'biophysics-structural-biology' => function() {
                    // TODO: maybe pass in a ready Builder::for(SomeModel::class)?
                    return self::for(Subject::class)
                        ->withId('biophysics-structural-biology')
                        ->withName('Biophysics and Structural Biology');
                },
            ],
        ];
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
