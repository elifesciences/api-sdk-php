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
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

function selectField($resultPromise, $fieldName, $default = null)
{
    return $resultPromise->then(function (Result $entity) use ($fieldName, $default) {
        if ($default !== null) {
            return $entity[$fieldName] ?? $default;
        } else {
            return $entity[$fieldName];
        }
    });
}

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
        if (!empty($context['snippet'])) {
            $collection = $this->denormalizeSnippet($data);

            $data['image']['banner'] = $collection
                ->then(function (Result $collection) {
                    return $collection['image']['banner'];
                });

            $data['curators'] = new PromiseSequence(selectField($collection, 'curators'));
            $data['content'] = new PromiseSequence(selectField($collection, 'content'));
            $data['relatedContent'] = new PromiseSequence(selectField($collection, 'relatedContent', []));
            $data['podcastEpisodes'] = new PromiseSequence(selectField($collection, 'podcastEpisodes'));
        } else {
            $data['image']['banner'] = promise_for($data['image']['banner']);
            $data['curators'] = new ArraySequence($data['curators']);
            $data['content'] = new ArraySequence($data['content']);
            $data['relatedContent'] = new ArraySequence($data['relatedContent'] ?? []);
            $data['podcastEpisodes'] = new ArraySequence($data['podcastEpisodes'] ?? []);
        }

        $denormalizePromise = function ($promise, $class, $format, $context) {
            return $promise->then(function (array $entity) use ($class, $format, $context) {
                return $this->denormalizer->denormalize($entity, $class, $format, $context);
            });
        };
        $data['image']['banner'] = $denormalizePromise($data['image']['banner'], Image::class, $format, $context);

        $denormalizeSequence = function ($sequence, $class, $format, $context) {
            return $sequence->map(function (array $entity) use ($class, $format, $context) {
                return $this->denormalizer->denormalize($entity, $class, $format, $context);
            });
        };
        $data['curators'] = $denormalizeSequence($data['curators'], Person::class, $format, $context + ['snippet' => true]);

        $denormalizeArray = function ($array, $class, $format, $context) {
            return new ArraySequence(array_map(function (array $subject) use ($class, $format, $context) {
                return $this->denormalizer->denormalize($subject, $class, $format, $context);
            }, $array));
        };
        $data['subjects'] = $denormalizeArray($data['subjects'] ?? [], Subject::class, $format, $context + ['snippet' => true]);
        $selectedCuratorEtAl = $data['selectedCurator']['etAl'] ?? false;
        $data['selectedCurator'] = $this->denormalizer->denormalize($data['selectedCurator'], Person::class, $format, $context + ['snippet' => true]);

        $data['content'] = $data['content']->map($contentItemDenormalization = function ($eachContent) use ($format, $context) {
            if ($eachContent['type'] == 'research-article') {
                if ($eachContent['status'] == 'poa') {
                    return $this->denormalizer->denormalize($eachContent, ArticlePoA::class, $format, $context + ['snippet' => true]);
                } else {
                    return $this->denormalizer->denormalize($eachContent, ArticleVoR::class, $format, $context + ['snippet' => true]);
                }
            } elseif ($eachContent['type'] == 'blog-article') {
                return $this->denormalizer->denormalize($eachContent, BlogArticle::class, $format, $context + ['snippet' => true]);
            } elseif ($eachContent['type'] == 'interview') {
                return $this->denormalizer->denormalize($eachContent, Interview::class, $format, $context + ['snippet' => true]);
            } else {
                throw new \LogicException("Cannot denormalize {$eachContent['type']}");
            }
        });
        $data['relatedContent'] = $data['relatedContent']->map($contentItemDenormalization);
        $data['podcastEpisodes'] = $denormalizeSequence($data['podcastEpisodes'], PodcastEpisode::class, $format, $context + ['snippet' => true]);

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
        //if (isset($this->found[$episode['number']])) {
        //    return $this->found[$episode['number']];
        //}

        $this->found[$collection['id']] = null;

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                foreach ($this->found as $id => $collection) {
                    if (null === $collection) {
                        $this->found[$id] = $this->collectionsClient->getCollection(
                            ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION, 1)],
                            $id
                        );
                    }
                }

                $this->globalCallback = null;

                return all($this->found)->wait();
            });
        }

        return $this->globalCallback
            ->then(function (array $collections) use ($collection) {
                return $collections[$collection['id']];
            });
    }

    public function normalize($object, $format = null, array $context = []) : array
    {
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
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        $data['selectedCurator'] = $this->normalizer->normalize($object->getSelectedCurator(), $format, ['snippet' => true]);
        if ($object->selectedCuratorEtAl()) {
            $data['selectedCurator']['etAl'] = $object->selectedCuratorEtAl();
        }

        if (empty($context['snippet'])) {
            $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);

            $data['curators'] = $object->getCurators()->map(function (Person $person) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($person, $format, $context);
            })->toArray();

            $contentNormalization = function ($eachContent) use ($format, $context) {
                if (!is_object($eachContent)) {
                    throw new LogicException('Content not valid: '.var_export($eachContent, true));
                }
                $context['snippet'] = true;

                $eachContentData = $this->normalizer->normalize($eachContent, $format, $context);
                if (method_exists($eachContent, 'getType')) {
                    $eachContentData['type'] = $eachContent->getType();
                } else {
                    $contentClasses = [
                        BlogArticle::class => 'blog-article',
                        Interview::class => 'interview',
                    ];
                    if (!array_key_exists(get_class($eachContent), $contentClasses)) {
                        throw new LogicException('Class of content '.get_class($eachContent).' is not supported in a Collection. Supported classes are: '.var_export($contentClasses, true));
                    }
                    $eachContentData['type'] = $contentClasses[get_class($eachContent)];
                }

                return $eachContentData;
            };

            $data['content'] = $object->getContent()->map($contentNormalization)->toArray();
            if (count($object->getRelatedContent()) > 0) {
                $data['relatedContent'] = $object->getRelatedContent()->map($contentNormalization)->toArray();
            }
            if (count($object->getPodcastEpisodes()) > 0) {
                $data['podcastEpisodes'] = $object->getPodcastEpisodes()->map(function (PodcastEpisode $podcastEpisode) use ($format, $context) {
                    $context['snippet'] = true;

                    return $this->normalizer->normalize($podcastEpisode, $format, $context);
                })->toArray();
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
