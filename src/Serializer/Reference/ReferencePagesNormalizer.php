<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use eLife\ApiSdk\Model\Reference\ReferencePages;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ReferencePagesNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = []) : ReferencePages
    {
        if (is_string($data)) {
            return new StringReferencePage($data);
        }

        return new ReferencePageRange($data['first'], $data['last'], $data['range']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return ReferencePages::class === $type;
    }

    /**
     * @param ReferencePages $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof ReferencePageRange) {
            return [
                'first' => $object->getFirst(),
                'last' => $object->getLast(),
                'range' => $object->toString(),
            ];
        }

        return $object->toString();
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ReferencePages;
    }
}
