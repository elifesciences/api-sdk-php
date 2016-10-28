<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A better name is welcome.
 */
final class NormalizationHelper
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var string|null
     */
    private $format;

    /**
     * @param string|null $format
     */
    public function __construct(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer, $format)
    {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->format = $format;
    }

    /**
     * @param string $fieldPath e.g. 'title' or 'image.banner'
     */
    public function selectField(PromiseInterface $resultPromise, string $fieldPath, $default = null) : PromiseInterface
    {
        $selectors = explode('.', $fieldPath);
        return $resultPromise->then(function (Result $entity) use (/*array*/ $selectors, $default) {
            $result = $entity->toArray();
            foreach ($selectors as $selector) {
                if (array_key_exists($selector, $result)) {
                    $result = $result[$selector];
                } else {
                    return $default;
                }
            }

            return $result;
        });
    }

    public function denormalizePromise(PromiseInterface $promise, string $class, $context) : PromiseInterface
    {
        return $promise->then(function (array $entity) use ($class, $context) {
            return $this->denormalizer->denormalize($entity, $class, $this->format, $context);
        });
    }

    public function denormalizeSequence(Sequence $sequence, string $class, $context) : Sequence
    {
        return $sequence->map(function (array $entity) use ($class, $context) {
            return $this->denormalizer->denormalize($entity, $class, $this->format, $context);
        });
    }

    public function denormalizeArray(array $array, string $class, $context) : ArraySequence
    {
        return new ArraySequence(array_map(function (array $subject) use ($class, $context) {
            return $this->denormalizer->denormalize($subject, $class, $this->format, $context);
        }, $array));
    }

    public function normalizeSequenceToSnippets(Sequence $sequence, $context) : array
    {
        return $sequence->map(function ($each) use ($context) {
            $context['snippet'] = true;

            return $this->normalizer->normalize($each, $this->format, $context);
        })->toArray();
    }

    public function normalizeToSnippet($object) : array
    {
        return $this->normalizer->normalize($object, $this->format, ['snippet' => true]);
    }
}
