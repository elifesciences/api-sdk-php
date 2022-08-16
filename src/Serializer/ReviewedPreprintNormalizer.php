<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ReviewedPreprint;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ReviewedPreprintNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{

    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : ReviewedPreprint
    {
        return new ReviewedPreprint(
            $data['id'],
            $data['title'],
            $data['status'],
            $data['stage'],
            $data['doi'],
            $data['authorLine'],
            $data['titlePrefix'],
            new \DateTimeImmutable($data['published']),
            new \DateTimeImmutable($data['reviewedDate']),
            new \DateTimeImmutable($data['statusDate']),
            $data['volume'],
            $data['elocationId'],
            $data['pdf'],
            $data['type'],
            new ArraySequence($data['subjects']),
            new ArraySequence($data['curationLabels']),
            new ArraySequence($data['image'])
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
         return (isset($data['type']) && $data['type'] == 'reviewed-preprint');
    }

    /**
     * @param ReviewedPreprint
     * TODO: Complete this method
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ReviewedPreprint;
    }
}
