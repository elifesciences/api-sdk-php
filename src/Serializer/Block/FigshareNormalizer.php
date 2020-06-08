<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figshare;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FigshareNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Figshare
    {
        return new Figshare(
            $data['id'],
            $data['title'],
            $data['width'],
            $data['height']
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Figshare::class === $type
            ||
            (Block::class === $type && 'figshare' === $data['type']);
    }

    /**
     * @param Figshare $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'figshare',
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'width' => $object->getWidth(),
            'height' => $object->getHeight(),
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Figshare;
    }
}
