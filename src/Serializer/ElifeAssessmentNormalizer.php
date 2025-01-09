<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\ElifeAssessment;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ElifeAssessmentNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : ElifeAssessment
    {
        $title = $data['title'];
        $significance = $data['significance'] ?? null;
        $strength = $data['strength'] ?? null;
        return new ElifeAssessment($title, $significance, $strength);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return ElifeAssessment::class === $type;
    }

    /**
     * @param ElifeAssessment $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'title' => $object->getTitle(),
            'significance' => $object->getSignificance(),
            'strength' => $object->getStrength(),
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ElifeAssessment;
    }
}
