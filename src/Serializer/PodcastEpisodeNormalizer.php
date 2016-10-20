<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Image;
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
        } else {
            $data['chapters'] = new ArraySequence($data['chapters']);
        }

        $data['chapters'] = $data['chapters']
            ->map(function (array $chapter) use ($format, $context) {
                return new PodcastEpisodeChapter($chapter['number'], $chapter['title'], $chapter['time'],
                    $chapter['impactStatement'] ?? null,
                    new ArraySequence(array_map(function (array $item) use ($format, $context) {
                        $context['snippet'] = true;

                        switch ($item['type']) {
                            case 'correction':
                            case 'editorial':
                            case 'feature':
                            case 'insight':
                            case 'research-advance':
                            case 'research-article':
                            case 'research-exchange':
                            case 'retraction':
                            case 'registered-report':
                            case 'replication-study':
                            case 'short-report':
                            case 'tools-resources':
                                if ('poa' === $item['status']) {
                                    $class = ArticlePoA::class;
                                } else {
                                    $class = ArticleVoR::class;
                                }
                                break;
                        }

                        return $this->denormalizer->denormalize($item, $class, $format, $context);
                    }, $chapter['content'])));
            });

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
            $this->denormalizer->denormalize($data['image'], Image::class, $format, $context),
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
        return PodcastEpisode::class === $type;
    }

    /**
     * @param PodcastEpisode $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'number' => $object->getNumber(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(DATE_ATOM),
            'image' => $this->normalizer->normalize($object->getImage(), $format, $context),
            'sources' => array_map(function (PodcastEpisodeSource $source) {
                return [
                    'mediaType' => $source->getMediaType(),
                    'uri' => $source->getUri(),
                ];
            }, $object->getSources()),
        ];

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (count($object->getSubjects()) > 0) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        if (empty($context['snippet'])) {
            $data['chapters'] = $object->getChapters()->map(function (PodcastEpisodeChapter $chapter) use (
                $format,
                $context
            ) {
                $data = [
                    'number' => $chapter->getNumber(),
                    'title' => $chapter->getTitle(),
                    'time' => $chapter->getTime(),
                    'content' => $chapter->getContent()->map(function ($item) use ($format, $context) {
                        $context['snippet'] = true;

                        return $this->normalizer->normalize($item, $format, $context);
                    })->toArray(),
                ];

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
