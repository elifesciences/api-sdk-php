<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use UnexpectedValueException;
use function GuzzleHttp\Promise\promise_for;

final class PodcastEpisodeChapterModelNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : PodcastEpisodeChapterModel
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

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            PodcastEpisodeChapterModel::class === $type
            ||
            Model::class === $type && 'podcast-episode-chapter' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param PodcastEpisodeChapterModel $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $chapter = [
            'number' => $object->getChapter()->getNumber(),
            'title' => $object->getChapter()->getTitle(),
            'time' => $object->getChapter()->getTime(),
        ];

        if ($object->getChapter()->getLongTitle()) {
            $chapter['longTitle'] = $object->getChapter()->getLongTitle();
        }

        if ($object->getChapter()->getImpactStatement()) {
            $chapter['impactStatement'] = $object->getChapter()->getImpactStatement();
        }

        return [
            'type' => 'podcast-episode-chapter',
            'episode' => $normalizationHelper->normalizeToSnippet($object->getEpisode()),
            'chapter' => $chapter,
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PodcastEpisodeChapterModel;
    }
}
