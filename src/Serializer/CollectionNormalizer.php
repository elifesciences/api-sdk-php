<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
//use eLife\ApiClient\MediaType;
//use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
//use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Interview;
//use eLife\ApiSdk\Model\CollectionChapter;
//use eLife\ApiSdk\Model\CollectionSource;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\Subject;
//use eLife\ApiSdk\Promise\CallbackPromise;
//use GuzzleHttp\Promise\PromiseInterface;
use LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
//use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

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
        $data['image']['banner'] = promise_for($data['image']['banner']);
        $data['image']['banner'] = $data['image']['banner']
            ->then(function (array $banner) use ($format, $context) {
                return $this->denormalizer->denormalize($banner, Image::class, $format, $context);
            });

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            $context['snippet'] = true;

            return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
        }, $data['subjects'] ?? []));

        return new Collection(
            $data['id'],
            $data['title'],
            promise_for($data['subTitle']),
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']),
            promise_for($data['image']['banner']),
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class, $format, $context),
            $data['subjects']
        );
    }

    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [];
        $data['id'] = $object->getId();
        $data['title'] = $object->getTitle();
        $data['subTitle'] = $object->getSubTitle();
        $data['impactStatement'] = $object->getImpactStatement();
        $data['updated'] = $object->getPublishedDate()->format(DATE_ATOM);

        $data['image']['thumbnail'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);
        if (count($object->getSubjects()) > 0) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        $data['selectedCurator'] = $this->normalizer->normalize($object->getSelectedCurator(), $format, ['snippet' => true]);
        $data['selectedCurator']['etAl'] = $object->selectedCuratorEtAl();

        $data['curators'] = $object->getCurators()->map(function (Person $person) use ($format, $context) {
            $context['snippet'] = true;

            return $this->normalizer->normalize($person, $format, $context);
        })->toArray();

        $contentNormalization = function ($eachContent) use ($format, $context) {
            if (!is_object($eachContent)) {
                throw new LogicException("Content not valid: " . var_export($eachContent, true));
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
                    throw new LogicException("Class of content " . get_class($eachContent) . " is not supported in a Collection. Supported classes are: " . var_export($contentClasses, true));
                }
                $eachContentData['type'] = $contentClasses[get_class($eachContent)];
            }
            return $eachContentData;
        };
        $data['content'] = $object->getContent()->map($contentNormalization)->toArray();
        $data['relatedContent'] = $object->getRelatedContent()->map($contentNormalization)->toArray();
        
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
