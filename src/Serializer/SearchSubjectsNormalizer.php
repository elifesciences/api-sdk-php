<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\SearchSubjects;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

class SearchSubjectsNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : SearchSubjects
    {
        $subjects = [];
        $counters = [];

        foreach ($data as $each) {
            $counters[] = $each['results'];
            unset($each['results']);
            $subjects[] = $this->denormalizer->denormalize($each, Subject::class, $format, $context + ['snippet' => true]);
        }

        return new SearchSubjects($subjects, $counters);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return SearchSubjects::class === $type;
    }

    /**
     * @param SearchSubjects $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [];
        foreach ($object as $subject => $results) {
            $data[] = array_merge(
                $this->normalizer->normalize($subject, $format, $context + ['snippet' => true]),
                [
                    'results' => $results,
                ]
            );
        }
        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof SearchSubjects;
    }
    
}
