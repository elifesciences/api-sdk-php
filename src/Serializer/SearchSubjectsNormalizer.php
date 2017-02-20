<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\SearchSubjects;
use eLife\ApiSdk\Model\Subject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SearchSubjectsNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : SearchSubjects
    {
        $subjects = [];
        $results = [];

        foreach ($data as $each) {
            $results[] = $each['results'];
            unset($each['results']);
            $subjects[] = $this->denormalizer->denormalize($each, Subject::class, $format, $context + ['snippet' => true]);
        }

        return new SearchSubjects($subjects, $results);
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
