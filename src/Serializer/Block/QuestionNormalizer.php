<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Question;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class QuestionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Question
    {
        return new Question($data['question'], array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['answer']));
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Question::class === $type
            ||
            (Block::class === $type && 'question' === $data['type']);
    }

    /**
     * @param Question $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'type' => 'question',
            'question' => $data->getQuestion(),
            'answer' => array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $data->getAnswer()),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Question;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Question::class => false,
            Block::class => false,
        ];
    }
}
