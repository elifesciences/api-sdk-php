<?php

namespace test\eLife\ApiSdk;

use BadMethodCallException;
use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Subject;
use InvalidArgumentException;
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
            throw new BadMethodCallException($name);
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
        foreach ($constructorArgumentNames as $name) {
            $constructorArguments[] = $this->testData[$name];
        }
        $instance = $class->newInstanceArgs($constructorArguments);

        return $instance;
    }

    public function sample($sampleName)
    {
        $samples = $this->samples();

        return call_user_func($samples[$this->model][$sampleName]);
    }

    private function defaultTestDataFor($model)
    {
        // TODO: turn into private field
        $defaults = [
            BlogArticle::class => function() {
                return [
                    'id' => '359325',
                    'title' => 'Media coverage: Slime can see',
                    'published' => new DateTimeImmutable(),
                    'impactStatement' => null,
                    'content' => new PromiseSequence(rejection_for('no content')),
                    'subjects' => new ArraySequence([]),
                ];
            },
            Collection::class => function() {
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
                ];
            },
            Image::class => function() {
                return [
                    'altText' => '',
                    'sizes' => [],
                ];
            },
            Subject::class => function() {
                return [
                    'id' => 'subject1',
                    'name' => 'Subject 1',
                    'impactStatement' => rejection_for('No impact statement'),
                    'banner' => rejection_for('No banner'),
                    'thumbnail' => rejection_for('No thumbnail'),
                ];
            },
            Person::class => function() {
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
            'eLife\ApiSdk\Model\Image' => [
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
}
