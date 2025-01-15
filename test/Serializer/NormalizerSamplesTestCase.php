<?php

namespace test\eLife\ApiSdk\Serializer;

use function file_get_contents;
use function gettype;
use function is_array;
use function JmesPath\search;
use LogicException;
use function substr;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

trait NormalizerSamplesTestCase
{
    /** @var NormalizerInterface&DenormalizerInterface */
    private $normalizer;

    /**
     * @test
     * @dataProvider sampleProvider
     */
    final public function it_denormalize_samples(string $original, array $context = [])
    {
        $denormalized = $this->normalizer->denormalize(json_decode($original, true), $this->class(), null, $context);
        $normalized = $this->normalizer->normalize($denormalized, null, $context);

        $this->assertEquals(json_decode($original, true), $normalized);
    }

    final public function sampleProvider()
    {
        foreach ($this->samples() as $path) {
            if (is_array($path)) {
                list($path, $context) = $path;
            } else {
                $context = [];
            }

            list($path, $pointer) = explode('#', $path.'#');

            foreach (glob($path) as $sample) {
                $contents = file_get_contents($sample);

                if ('' === $pointer) {
                    yield $sample => [$contents, $context];

                    continue;
                }

                $values = search($pointer, json_decode($contents, true));

                if ('[]' === substr($pointer, -2)) {
                    $pointer = substr($pointer, 0, -2);
                }

                if (!is_array($values)) {
                    throw new LogicException('Expected an array, got '.gettype($values)." for {$sample}#{$pointer}");
                }

                if (!isset($context['snippet'])) {
                    $context['snippet'] = true;
                }

                foreach ($values as $i => $value) {
                    if (!isset($context['type']) && isset($value['type'])) {
                        $context['type'] = true;
                    }

                    yield "{$sample}#{$pointer}[{$i}]" => [json_encode($value), $context];
                }
            }
        }
    }

    abstract protected function class() : string;

    abstract protected function samples();
}
