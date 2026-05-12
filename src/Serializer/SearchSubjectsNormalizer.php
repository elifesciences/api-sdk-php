<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\SearchSubjects;
use eLife\ApiSdk\Model\Subject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class SearchSubjectsNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : SearchSubjects
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

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return SearchSubjects::class === $type;
    }

    /**
     * @param SearchSubjects $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [];
        foreach ($data as $subject => $results) {
            $arr[] = array_merge(
                $this->normalizer->normalize($subject, $format, $context + ['snippet' => true]),
                [
                    'results' => $results,
                ]
            );
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof SearchSubjects;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            SearchSubjects::class => true,
        ];
    }
}
