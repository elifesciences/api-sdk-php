<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Tweet;
use eLife\ApiSdk\Model\Date;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TweetNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Tweet
    {
        return new Tweet(
            $data['id'],
            Date::fromString($data['date']),
            $data['text'],
            $data['accountId'],
            $data['accountLabel'],
            $data['conversation'] ?? false,
            $data['mediaCard'] ?? false
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Tweet::class === $type
            ||
            (Block::class === $type && 'tweet' === $data['type']);
    }

    /**
     * @param Tweet $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'tweet',
            'id' => $data->getId(),
            'date' => $data->getDate()->toString(),
            'text' => $data->getText(),
            'accountId' => $data->getAccountId(),
            'accountLabel' => $data->getAccountLabel(),
            'conversation' => $data->isConversation(),
            'mediaCard' => $data->isMediaCard(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Tweet;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Tweet::class => false,
            Block::class => false,
        ];
    }
}
