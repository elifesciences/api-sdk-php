<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;
use LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

/**
 * A better name is welcome.
 */
class NormalizationHelper
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

    public function selectField(PromiseInterface $resultPromise, $fieldName, $default = null) : PromiseInterface
    {
        return $resultPromise->then(function (Result $entity) use ($fieldName, $default) {
            if ($default !== null) {
                return $entity[$fieldName] ?? $default;
            } else {
                return $entity[$fieldName];
            }
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
