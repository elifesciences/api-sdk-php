<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class CollectionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(CollectionsClient $collectionsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $collection) : string {
                return $collection['id'];
            },
            function (string $id) use ($collectionsClient) : PromiseInterface {
                return $collectionsClient->getCollection(
                    ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION, 1)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Collection
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        if (!empty($context['snippet'])) {
            $collection = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['subTitle'] = $normalizationHelper->selectField($collection, 'subTitle');
            $data['image']['banner'] = $normalizationHelper->selectField($collection, 'image.banner');
            $data['curators'] = new PromiseSequence($normalizationHelper->selectField($collection, 'curators'));
            $data['content'] = new PromiseSequence($normalizationHelper->selectField($collection, 'content'));
            $data['relatedContent'] = new PromiseSequence($normalizationHelper->selectField($collection, 'relatedContent', []));
            $data['podcastEpisodes'] = new PromiseSequence($normalizationHelper->selectField($collection, 'podcastEpisodes'));
        } else {
            $data['subTitle'] = promise_for($data['subTitle'] ?? null);
            $data['image']['banner'] = promise_for($data['image']['banner']);
            $data['curators'] = new ArraySequence($data['curators']);
            $data['content'] = new ArraySequence($data['content']);
            $data['relatedContent'] = new ArraySequence($data['relatedContent'] ?? []);
            $data['podcastEpisodes'] = new ArraySequence($data['podcastEpisodes'] ?? []);
        }

        //$data['subTitle'] = $normalizationHelper->denormalizePromise($data['subTitle'], string?, $context);
        $data['image']['banner'] = $normalizationHelper->denormalizePromise($data['image']['banner'], Image::class, $context);

        $data['curators'] = $normalizationHelper->denormalizeSequence($data['curators'], Person::class, $context + ['snippet' => true]);

        $data['subjects'] = $normalizationHelper->denormalizeArray($data['subjects'] ?? [], Subject::class, $context + ['snippet' => true]);
        $selectedCuratorEtAl = $data['selectedCurator']['etAl'] ?? false;
        $data['selectedCurator'] = $this->denormalizer->denormalize($data['selectedCurator'], Person::class, $format, $context + ['snippet' => true]);

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

        return new Collection(
            $data['id'],
            $data['title'],
            $data['subTitle'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            promise_for($data['image']['banner']),
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class, $format, $context),
            $data['subjects'],
            $data['selectedCurator'],
            $selectedCuratorEtAl,
            $data['curators'],
            $data['content'],
            $data['relatedContent'],
            $data['podcastEpisodes']
        );
    }

    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [];
        if (!empty($context['type'])) {
            $data['type'] = 'collection';
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

        $data['selectedCurator'] = $normalizationHelper->normalizeToSnippet($object->getSelectedCurator());
        if ($object->selectedCuratorEtAl()) {
            $data['selectedCurator']['etAl'] = $object->selectedCuratorEtAl();
        }

        if (empty($context['snippet'])) {
            if ($object->getSubTitle()) {
                $data['subTitle'] = $object->getSubTitle();
            }

            $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);

            $typeContext = array_merge($context, ['type' => true]);

            $data['curators'] = $normalizationHelper->normalizeSequenceToSnippets($object->getCurators(), $context);

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
            Collection::class === $type
            ||
            'collection' === ($data['type'] ?? 'unknown');
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Collection;
    }
}
