<?php

namespace eLife\ApiSdk\Serializer;

#use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
#use eLife\ApiClient\MediaType;
#use eLife\ApiClient\Result;
#use eLife\ApiSdk\Collection\ArraySequence;
#use eLife\ApiSdk\Collection\PromiseSequence;
#use eLife\ApiSdk\Model\ArticlePoA;
#use eLife\ApiSdk\Model\ArticleVoR;
#use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Collection;
#use eLife\ApiSdk\Model\CollectionChapter;
#use eLife\ApiSdk\Model\CollectionSource;
#use eLife\ApiSdk\Model\Subject;
#use eLife\ApiSdk\Promise\CallbackPromise;
#use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
#use function GuzzleHttp\Promise\all;
#use function GuzzleHttp\Promise\promise_for;

final class CollectionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $collectionsClient;
    private $found = [];
    private $globalCallback;

    public function __construct(CollectionsClient $collectionsClient)
    {
        $this->collectionsClient = $collectionsClient;
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Collection
    {
    }

    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [];
        $data['id'] = $object->getId();
        $data['title'] = $object->getTitle();
        $data['impact_statement'] = $object->getImpactStatement();
        $data['updated'] = $object->getPublishedDate()->format(DATE_ATOM);

        $data['image']['thumbnail'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Collection::class === $type;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Collection;
    }
}
