<?php

namespace test\eLife\ApiSdk\Serializer;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

trait NormalizerTestCase
{
    /** @var NormalizerInterface&DenormalizerInterface */
    private $normalizer;

    /**
     * @test
     * @dataProvider sampleProvider
     */
    final public function it_denormalize_samples(string $original)
    {
        $denormalized = $this->normalizer->denormalize(json_decode($original, true), $this->class());
        $normalized = $this->normalizer->normalize($denormalized);

        $this->assertJsonStringEqualsJsonString($original, json_encode($normalized));
    }

    final public function sampleProvider()
    {
        $samples = Finder::create()->in($this->samples());

        foreach ($samples as $sample) {
            yield $sample->getFilenameWithoutExtension() => [$sample->getContents()];
        }
    }

    abstract protected function class() : string;

    abstract protected function samples() : string;
}
