<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class PodcastEpisodeNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $podcastClient;
    private $found = [];
    private $globalCallback;

    public function __construct(PodcastClient $podcastClient)
    {
        $this->podcastClient = $podcastClient;
    }

    public function denormalize($data, $class, $format = null, array $context = []) : PodcastEpisode
    {
        if (!empty($context['snippet'])) {
            $podcastEpisode = $this->denormalizeSnippet($data);

            $data['chapters'] = new PromiseSequence($podcastEpisode
                ->then(function (Result $podcastEpisode) {
                    return $podcastEpisode['chapters'];
                }));

            $data['image']['banner'] = $podcastEpisode
                ->then(function (Result $podcastEpisode) {
                    return $podcastEpisode['image']['banner'];
                });
        } else {
            $data['chapters'] = new ArraySequence($data['chapters']);

            $data['image']['banner'] = promise_for($data['image']['banner']);
        }

        $data['chapters'] = $data['chapters']
            ->map(function (array $chapter) use ($format, $context) {
                return new PodcastEpisodeChapter($chapter['number'], $chapter['title'], $chapter['time'],
                    $chapter['impactStatement'] ?? null,
                    new ArraySequence(array_map(function (array $item) use ($format, $context) {
                        $context['snippet'] = true;

                        return $this->denormalizer->denormalize($item, Model::class, $format, $context);
                    }, $chapter['content'] ?? [])));
            });

        $data['image']['banner'] = $data['image']['banner']
            ->then(function (array $banner) use ($format, $context) {
                return $this->denormalizer->denormalize($banner, Image::class, $format, $context);
            });

        $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class,
            $format, $context);

        $data['sources'] = array_map(function (array $source) {
            return new PodcastEpisodeSource($source['mediaType'], $source['uri']);
        }, $data['sources']);

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            $context['snippet'] = true;

            return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
        }, $data['subjects'] ?? []));

        return new PodcastEpisode(
            $data['number'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            $data['image']['banner'],
            $data['image']['thumbnail'],
            $data['sources'],
            $data['subjects'],
            $data['chapters']
        );
    }

    private function denormalizeSnippet(array $episode) : PromiseInterface
    {
        if (isset($this->found[$episode['number']])) {
            return $this->found[$episode['number']];
        }

        $this->found[$episode['number']] = null;

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                foreach ($this->found as $number => $episode) {
                    if (null === $episode) {
                        $this->found[$number] = $this->podcastClient->getEpisode(
                            ['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, 1)],
                            $number
                        );
                    }
                }

                $this->globalCallback = null;

                return all($this->found)->wait();
            });
        }

        return $this->globalCallback
            ->then(function (array $interviews) use ($episode) {
                return $interviews[$episode['number']];
            });
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            PodcastEpisode::class === $type
            ||
            Model::class === $type && 'podcast-episode' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param PodcastEpisode $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [
            'number' => $object->getNumber(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(DATE_ATOM),
            'image' => ['thumbnail' => $this->normalizer->normalize($object->getThumbnail(), $format, $context)],
            'sources' => array_map(function (PodcastEpisodeSource $source) {
                return [
                    'mediaType' => $source->getMediaType(),
                    'uri' => $source->getUri(),
                ];
            }, $object->getSources()),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'podcast-episode';
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if ($object->getSubjects()->notEmpty()) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        if (empty($context['snippet'])) {
            $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);

            $data['chapters'] = $object->getChapters()->map(function (PodcastEpisodeChapter $chapter) use (
                $format,
                $context,
                $normalizationHelper
            ) {
                $typeContext = array_merge($context, ['type' => true]);

                $data = [
                    'number' => $chapter->getNumber(),
                    'title' => $chapter->getTitle(),
                    'time' => $chapter->getTime(),
                ];
                if ($chapter->getContent()->notEmpty()) {
                    $data['content'] = $normalizationHelper->normalizeSequenceToSnippets($chapter->getContent(), $typeContext);
                }

                if ($chapter->getImpactStatement()) {
                    $data['impactStatement'] = $chapter->getImpactStatement();
                }

                return $data;
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PodcastEpisode;
    }
}
