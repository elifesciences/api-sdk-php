<?php

namespace test\eLife\ApiSdk;

use BadMethodCallException;
use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Collection;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

class Builder
{
    private $model;
    private $testData;

    public function create($model)
    {
        $this->model = $model;
        $this->testData = [
            'id' => 'tropical-disease',
            'title' => 'Tropical disease',
            'subTitle' => rejection_for('Tropical disease subtitle'),
            'impactStatement' => null,
            'publishedDate' => new DateTimeImmutable(),
            'banner' => rejection_for('No banner'),
            'thumbnail' => new Image('', [900 => 'https://placehold.it/900x450']),
            'subjects' => new ArraySequence([]),
        ];
    
        if (strstr($model, 'Subject')) {
            $this->testData = [
                'id' => 'subject1',
                'name' => 'Subject 1',
                'impactStatement' => rejection_for('No impact statement'),
                'banner' => rejection_for('No banner'),
                'thumbnail' => rejection_for('No thumbnail'),
            ];
        }
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
