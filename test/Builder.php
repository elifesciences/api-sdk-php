<?php

namespace test\eLife\ApiSdk;

use BadMethodCallException;
use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Collection;
use InvalidArgumentException;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

class Builder
{
    private $model;
    private $testData;

    public static function for($model)
    {
        return (new self())->create($model);
    }
    
    public function create($model)
    {
        $this->model = $model;
        $this->testData = $this->defaultTestDataFor($model);
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
        $constructorArgumentNames = array_map(function($p) { return $p->getName(); } , $class->getConstructor()->getParameters());
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
            'eLife\ApiSdk\Model\Collection' => [
                'id' => 'tropical-disease',
                'title' => 'Tropical disease',
                'subTitle' => rejection_for('Tropical disease subtitle'),
                'impactStatement' => null,
                'publishedDate' => new DateTimeImmutable(),
                'banner' => rejection_for('No banner'),
                'thumbnail' => new Image('', [900 => 'https://placehold.it/900x450']),
                'subjects' => new ArraySequence([]),
            ],
            'eLife\ApiSdk\Model\Image' => [
                'altText' => '',
                'sizes' => [],
            ],
            'eLife\ApiSdk\Model\Subject' => [
                'id' => 'subject1',
                'name' => 'Subject 1',
                'impactStatement' => rejection_for('No impact statement'),
                'banner' => rejection_for('No banner'),
                'thumbnail' => rejection_for('No thumbnail'),
            ],
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
                'banner' => function() {
                    return new Image(
                        '',
                        [new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900'])]
                    );
                },
                'thumbnail' => function() {
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
                }
            ]
        ];
    }

    private function ensureExistingField($field)
    {
        $allowedFields = array_keys($this->testData);
        if (!in_array($field, $allowedFields)) {
            throw new BadMethodCallException("Field $field is not allowed for {$this->model}. Allowed fields: " . implode(', ', $allowedFields));
        }
    }

    private function ensureSingleArgument($args)
    {
        if (count($args) > 1) {
            throw new BadMethodCallException("Too many arguments: " . var_export($args, true));
        }
    }
}
