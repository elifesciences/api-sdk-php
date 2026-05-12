<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\CollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\Subject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class CollectionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(CollectionsClient $collectionsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $collection) : string {
                return $collection['id'];
            },
            function (string $id) use ($collectionsClient) : PromiseInterface {
                return $collectionsClient->getCollection(
                    ['Accept' => (string) new MediaType(CollectionsClient::TYPE_COLLECTION, Collections::VERSION_COLLECTION)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : Collection
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        if (!empty($context['snippet'])) {
            $collection = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['image']['banner'] = $normalizationHelper->selectField($collection, 'image.banner');
            $data['image']['social'] = $normalizationHelper->selectField($collection, 'image.social');
            $data['curators'] = new PromiseSequence($normalizationHelper->selectField($collection, 'curators'));
            $data['summary'] = new PromiseSequence($normalizationHelper->selectField($collection, 'summary'));
            $data['content'] = new PromiseSequence($normalizationHelper->selectField($collection, 'content'));
            $data['relatedContent'] = new PromiseSequence($normalizationHelper->selectField($collection, 'relatedContent', []));
            $data['podcastEpisodes'] = new PromiseSequence($normalizationHelper->selectField($collection, 'podcastEpisodes'));
        } else {
            $data['image']['banner'] = promise_for($data['image']['banner']);
            $data['image']['social'] = promise_for($data['image']['social'] ?? null);
            $data['curators'] = new ArraySequence($data['curators']);
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

        $data['curators'] = $normalizationHelper->denormalizeSequence($data['curators'], Person::class, $context + ['snippet' => true]);

        $data['subjects'] = $normalizationHelper->denormalizeArray($data['subjects'] ?? [], Subject::class, $context + ['snippet' => true]);
        $selectedCuratorEtAl = $data['selectedCurator']['etAl'] ?? false;
        $data['selectedCurator'] = $this->denormalizer->denormalize($data['selectedCurator'], Person::class, $format, $context + ['snippet' => true]);
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

        return new Collection(
            $data['id'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            promise_for($data['image']['banner']),
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class, $format, $context),
            $data['image']['social'],
            $data['subjects'],
            $data['selectedCurator'],
            $selectedCuratorEtAl,
            $data['curators'],
            $data['summary'],
            $data['content'],
            $data['relatedContent'],
            $data['podcastEpisodes']
        );
    }


    /**
     * @param Collection $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $arr = [];
        if (!empty($context['type'])) {
            $arr['type'] = 'collection';
        }
        $arr['id'] = $data->getId();
        $arr['title'] = $data->getTitle();
        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }
        $arr['published'] = $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT);
        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        $arr['image']['thumbnail'] = $this->normalizer->normalize($data->getThumbnail(), $format, $context);
        if (!$data->getSubjects()->isEmpty()) {
            $arr['subjects'] = $normalizationHelper->normalizeSequenceToSnippets($data->getSubjects(), $context);
        }

        $arr['selectedCurator'] = $normalizationHelper->normalizeToSnippet($data->getSelectedCurator());
        if ($data->selectedCuratorEtAl()) {
            $arr['selectedCurator']['etAl'] = $data->selectedCuratorEtAl();
        }

        if (empty($context['snippet'])) {
            $arr['image']['banner'] = $this->normalizer->normalize($data->getBanner(), $format, $context);

            if ($data->getSocialImage()) {
                $arr['image']['social'] = $this->normalizer->normalize($data->getSocialImage(), $format, $context);
            }

            $typeContext = array_merge($context, ['type' => true]);

            $arr['curators'] = $normalizationHelper->normalizeSequenceToSnippets($data->getCurators(), $context);

            if ($data->getSummary()->notEmpty()) {
                $arr['summary'] = $data->getSummary()->map(function (Block $block) use ($format, $context) {
                    return $this->normalizer->normalize($block, $format, $context);
                })->toArray();
            }

            $arr['content'] = $normalizationHelper->normalizeSequenceToSnippets($data->getContent(), $typeContext);
            if (!$data->getRelatedContent()->isEmpty()) {
                $arr['relatedContent'] = $normalizationHelper->normalizeSequenceToSnippets($data->getRelatedContent(), $typeContext);
            }
            if (!$data->getPodcastEpisodes()->isEmpty()) {
                $arr['podcastEpisodes'] = $normalizationHelper->normalizeSequenceToSnippets($data->getPodcastEpisodes(), $context);
            }
        }

        return $arr;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Collection::class === $type
            ||
            'collection' === ($data['type'] ?? 'unknown');
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Collection;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Collection::class => false,
            Model::class => false,
        ];
    }
}
