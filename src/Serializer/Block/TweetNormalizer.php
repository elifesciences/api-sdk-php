<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Tweet;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TweetNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Tweet
    {
        return new Tweet(
            $data['id'],
            $data['date'],
            array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['text']),
            $data['accountId'],
            $data['accountLabel'],
            $data['conversation'] ?? false,
            $data['mediaCard'] ?? false
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Tweet::class === $type
            ||
            (Block::class === $type && 'tweet' === $data['type']);
    }

    /**
     * @param Tweet $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'tweet',
            'id' => $object->getId(),
            'date' => $object->getDate()->toString(),
            'text' => array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $object->getText()),
            'accountId' => $object->getAccountId(),
            'accountLabel' => $object->getAccountLabel(),
            $data['conversation'] ?? false,
            $data['mediaCard'] ?? false,
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Tweet;
    }
}
