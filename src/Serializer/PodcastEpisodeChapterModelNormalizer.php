<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use function GuzzleHttp\Promise\promise_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use UnexpectedValueException;

final class PodcastEpisodeChapterModelNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : PodcastEpisodeChapterModel
    {
        $data['episode'] = $this->denormalizer->denormalize($data['episode'], PodcastEpisode::class, $format, ['snippet' => true] + $context);

        $data['chapter'] = new PodcastEpisodeChapter($data['chapter']['number'], $data['chapter']['title'], $data['chapter']['longTitle'] ?? null, $data['chapter']['time'],
            $data['chapter']['impactStatement'] ?? null,
            new PromiseSequence(
                promise_for($data['episode']->getChapters())
                    ->then(function (Sequence $chapters) use ($data) {
                        foreach ($chapters as $chapter) {
                            if ($data['chapter']['number'] === $chapter->getNumber()) {
                                return $chapter->getContent();
                            }
                        }

                        throw new UnexpectedValueException('Could not find chapter');
                    })
            )
        );

        return new PodcastEpisodeChapterModel($data['episode'], $data['chapter']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            PodcastEpisodeChapterModel::class === $type
            ||
            Model::class === $type && 'podcast-episode-chapter' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param PodcastEpisodeChapterModel $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $chapter = [
            'number' => $data->getChapter()->getNumber(),
            'title' => $data->getChapter()->getTitle(),
            'time' => $data->getChapter()->getTime(),
        ];

        if ($data->getChapter()->getLongTitle()) {
            $chapter['longTitle'] = $data->getChapter()->getLongTitle();
        }

        if ($data->getChapter()->getImpactStatement()) {
            $chapter['impactStatement'] = $data->getChapter()->getImpactStatement();
        }

        return [
            'type' => 'podcast-episode-chapter',
            'episode' => $normalizationHelper->normalizeToSnippet($data->getEpisode()),
            'chapter' => $chapter,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof PodcastEpisodeChapterModel;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PodcastEpisodeChapter::class => false,
            Model::class => false,
        ];
    }
}
