<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\PromotionalCollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\PromotionalCollections;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PromotionalCollection;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PromotionalCollectionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(PromotionalCollectionsClient $promotionalCollectionsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $promotionalCollection) : string {
                return $promotionalCollection['id'];
            },
            function (string $id) use ($promotionalCollectionsClient) : PromiseInterface {
                return $promotionalCollectionsClient->getPromotionalCollection(
                    ['Accept' => (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION, PromotionalCollections::VERSION_PROMOTIONAL_COLLECTION)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : PromotionalCollection
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        if (!empty($context['snippet'])) {
            $promotionalCollection = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['image']['banner'] = $normalizationHelper->selectField($promotionalCollection, 'image.banner');
            $data['image']['social'] = $normalizationHelper->selectField($promotionalCollection, 'image.social');
            $data['editors'] = new PromiseSequence($normalizationHelper->selectField($promotionalCollection, 'editors'));
            $data['summary'] = new PromiseSequence($normalizationHelper->selectField($promotionalCollection, 'summary'));
            $data['content'] = new PromiseSequence($normalizationHelper->selectField($promotionalCollection, 'content'));
            $data['relatedContent'] = new PromiseSequence($normalizationHelper->selectField($promotionalCollection, 'relatedContent', []));
            $data['podcastEpisodes'] = new PromiseSequence($normalizationHelper->selectField($promotionalCollection, 'podcastEpisodes'));
        } else {
            $data['image']['banner'] = promise_for($data['image']['banner']);
            $data['image']['social'] = promise_for($data['image']['social'] ?? null);
            $data['editors'] = new ArraySequence($data['editors'] ?? []);
            $data['summary'] = new ArraySequence($data['summary'] ?? []);
            $data['content'] = new ArraySequence($data['content']);
            $data['relatedContent'] = new ArraySequence($data['relatedContent'] ?? []);
            $data['podcastEpisodes'] = new ArraySequence($data['podcastEpisodes'] ?? []);
        }

        $data['image']['banner'] = $normalizationHelper->denormalizePromise($data['image']['banner'], Image::class, $context);

        $data['image']['social'] = $data['image']['social']
            ->then(function ($socialImage) use ($format, $context) {
                return false === empty($socialImage) ? $this->denormalizer->denormalize($socialImage, Image::class, $format, $context) : null;
            });

        $data['editors'] = $normalizationHelper->denormalizeSequence($data['editors'], Person::class, $context + ['snippet' => true]);

        $data['subjects'] = $normalizationHelper->denormalizeArray($data['subjects'] ?? [], Subject::class, $context + ['snippet' => true]);
        $data['summary'] = $data['summary']->map(function (array $block) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $contentItemDenormalization = function ($eachContent) use ($format, $context) {
            return $this->denormalizer->denormalize(
                $eachContent,
                Model::class,
                $format,
                $context + ['snippet' => true]
            );
        };
        $data['content'] = $data['content']->map($contentItemDenormalization);
        $data['relatedContent'] = $data['relatedContent']->map($contentItemDenormalization);
        $data['podcastEpisodes'] = $normalizationHelper->denormalizeSequence($data['podcastEpisodes'], PodcastEpisode::class, $context + ['snippet' => true]);

        return new PromotionalCollection(
            $data['id'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            promise_for($data['image']['banner']),
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class, $format, $context),
            $data['image']['social'],
            $data['subjects'],
            $data['editors'],
            $data['summary'],
            $data['content'],
            $data['relatedContent'],
            $data['podcastEpisodes']
        );
    }

    /**
     * @param PromotionalCollection $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [];
        if (!empty($context['type'])) {
            $data['type'] = 'promotional-collection';
        }
        $data['id'] = $object->getId();
        $data['title'] = $object->getTitle();
        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }
        $data['published'] = $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT);
        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        $data['image']['thumbnail'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        if (!$object->getSubjects()->isEmpty()) {
            $data['subjects'] = $normalizationHelper->normalizeSequenceToSnippets($object->getSubjects(), $context);
        }

        if (empty($context['snippet'])) {
            $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);

            if ($object->getSocialImage()) {
                $data['image']['social'] = $this->normalizer->normalize($object->getSocialImage(), $format, $context);
            }

            $typeContext = array_merge($context, ['type' => true]);

            if ($object->getEditors()->notEmpty()) {
                $data['editors'] = $normalizationHelper->normalizeSequenceToSnippets($object->getEditors(), $context);
            }

            if ($object->getSummary()->notEmpty()) {
                $data['summary'] = $object->getSummary()->map(function (Block $block) use ($format, $context) {
                    return $this->normalizer->normalize($block, $format, $context);
                })->toArray();
            }

            $data['content'] = $normalizationHelper->normalizeSequenceToSnippets($object->getContent(), $typeContext);
            if (!$object->getRelatedContent()->isEmpty()) {
                $data['relatedContent'] = $normalizationHelper->normalizeSequenceToSnippets($object->getRelatedContent(), $typeContext);
            }
            if (!$object->getPodcastEpisodes()->isEmpty()) {
                $data['podcastEpisodes'] = $normalizationHelper->normalizeSequenceToSnippets($object->getPodcastEpisodes(), $context);
            }
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            PromotionalCollection::class === $type
            ||
            'promotional-collection' === ($data['type'] ?? 'unknown');
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PromotionalCollection;
    }
}
