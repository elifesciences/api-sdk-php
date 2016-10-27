<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
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
use function GuzzleHttp\Promise\promise_for;

final class CollectionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $collectionsClient;
    private $identityMap;
    private $globalCallback;

    public function __construct(CollectionsClient $collectionsClient)
    {
        $this->collectionsClient = $collectionsClient;
        $this->identityMap = new IdentityMap();
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Collection
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        if (!empty($context['snippet'])) {
            $collection = $this->denormalizeSnippet($data);

            $data['image']['banner'] = $collection
                ->then(function (Result $collection) {
                    return $collection['image']['banner'];
                });

            $data['curators'] = new PromiseSequence($normalizationHelper->selectField($collection, 'curators'));
            $data['content'] = new PromiseSequence($normalizationHelper->selectField($collection, 'content'));
            $data['relatedContent'] = new PromiseSequence($normalizationHelper->selectField($collection, 'relatedContent', []));
            $data['podcastEpisodes'] = new PromiseSequence($normalizationHelper->selectField($collection, 'podcastEpisodes'));
        } else {
            $data['image']['banner'] = promise_for($data['image']['banner']);
            $data['curators'] = new ArraySequence($data['curators']);
            $data['content'] = new ArraySequence($data['content']);
            $data['relatedContent'] = new ArraySequence($data['relatedContent'] ?? []);
            $data['podcastEpisodes'] = new ArraySequence($data['podcastEpisodes'] ?? []);
        }

        $data['image']['banner'] = $normalizationHelper->denormalizePromise($data['image']['banner'], Image::class, $context);

        $data['curators'] = $normalizationHelper->denormalizeSequence($data['curators'], Person::class, $context + ['snippet' => true]);

        $data['subjects'] = $normalizationHelper->denormalizeArray($data['subjects'] ?? [], Subject::class, $context + ['snippet' => true]);
        $selectedCuratorEtAl = $data['selectedCurator']['etAl'] ?? false;
        $data['selectedCurator'] = $this->denormalizer->denormalize($data['selectedCurator'], Person::class, $format, $context + ['snippet' => true]);

        $contentItemDenormalization = function ($eachContent) use ($format, $context) {
            if ($eachContent['type'] == 'research-article') {
                if ($eachContent['status'] == 'poa') {
                    $class = ArticlePoA::class;
                } else {
                    $class = ArticleVoR::class;
                }
            } elseif ($eachContent['type'] == 'blog-article') {
                $class = BlogArticle::class;
            } elseif ($eachContent['type'] == 'interview') {
                $class = Interview::class;
            } else {
                throw new \LogicException("Cannot denormalize {$eachContent['type']}");
            }

            return $this->denormalizer->denormalize(
                $eachContent,
                $class,
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
            promise_for($data['subTitle'] ?? null),
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']),
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

    private function denormalizeSnippet(array $collection) : PromiseInterface
    {
        if ($this->identityMap->has($collection['id'])) {
            return $this->identityMap->get($collection['id']);
        }

        $this->identityMap->reset($collection['id']);

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                $this->identityMap->fillMissingWith(function ($id) {
                    return $this->collectionsClient->getCollection(
                        ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION, 1)],
                        $id
                    );
                });

                $this->globalCallback = null;

                return $this->identityMap->waitForAll();
            });
        }

        return $this->globalCallback
            ->then(function (array $collections) use ($collection) {
                return $collections[$collection['id']];
            });
    }

    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [];
        $data['id'] = $object->getId();
        $data['title'] = $object->getTitle();
        if ($object->getSubTitle()) {
            $data['subTitle'] = $object->getSubTitle();
        }
        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }
        $data['updated'] = $object->getPublishedDate()->format(DATE_ATOM);

        $data['image']['thumbnail'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        if (count($object->getSubjects()) > 0) {
            $data['subjects'] = $normalizationHelper->normalizeSequenceToSnippets($object->getSubjects(), $context);
        }

        $data['selectedCurator'] = $normalizationHelper->normalizeToSnippet($object->getSelectedCurator());
        if ($object->selectedCuratorEtAl()) {
            $data['selectedCurator']['etAl'] = $object->selectedCuratorEtAl();
        }

        if (empty($context['snippet'])) {
            $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);

            $data['curators'] = $normalizationHelper->normalizeSequenceToSnippets($object->getCurators(), $context);

            $contentNormalization = function ($eachContent) use ($normalizationHelper) {
                $eachContentData = $normalizationHelper->normalizeToSnippet($eachContent);
                $contentClasses = [
                    ArticlePoA::class => 'research-article',
                    ArticleVoR::class => 'research-article',
                    BlogArticle::class => 'blog-article',
                    Interview::class => 'interview',
                ];
                if (!array_key_exists(get_class($eachContent), $contentClasses)) {
                    throw new LogicException('Class of content '.get_class($eachContent).' is not supported in a Collection. Supported classes are: '.var_export($contentClasses, true));
                }
                $eachContentData['type'] = $contentClasses[get_class($eachContent)];

                return $eachContentData;
            };

            $data['content'] = $object->getContent()->map($contentNormalization)->toArray();
            if (count($object->getRelatedContent()) > 0) {
                $data['relatedContent'] = $object->getRelatedContent()->map($contentNormalization)->toArray();
            }
            if (count($object->getPodcastEpisodes()) > 0) {
                $data['podcastEpisodes'] = $normalizationHelper->normalizeSequenceToSnippets($object->getPodcastEpisodes(), $context);
            }
        }

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
