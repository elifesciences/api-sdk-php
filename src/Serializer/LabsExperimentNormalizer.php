<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\ApiSdk\Model\Model;
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

final class LabsExperimentNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $labsClient;
    private $found = [];
    private $globalCallback;

    public function __construct(LabsClient $labsClient)
    {
        $this->labsClient = $labsClient;
    }

    public function denormalize($data, $class, $format = null, array $context = []) : LabsExperiment
    {
        if (!empty($context['snippet'])) {
            $experiment = $this->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($experiment
                ->then(function (Result $experiment) {
                    return $experiment['content'];
                }));

            $data['image']['banner'] = $experiment
                ->then(function (Result $article) {
                    return $article['image']['banner'];
                });
        } else {
            $data['content'] = new ArraySequence($data['content']);

            $data['image']['banner'] = promise_for($data['image']['banner']);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['image']['banner'] = $data['image']['banner']
            ->then(function (array $banner) use ($format, $context) {
                return $this->denormalizer->denormalize($banner, Image::class, $format, $context);
            });

        $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class,
            $format, $context);

        return new LabsExperiment(
            $data['number'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            $data['impactStatement'] ?? null,
            $data['image']['banner'],
            $data['image']['thumbnail'],
            $data['content']
        );
    }

    private function denormalizeSnippet(array $experiment) : PromiseInterface
    {
        if (isset($this->found[$experiment['number']])) {
            return $this->found[$experiment['number']];
        }

        $this->found[$experiment['number']] = null;

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                foreach ($this->found as $number => $experiment) {
                    if (null === $experiment) {
                        $this->found[$number] = $this->labsClient->getExperiment(
                            ['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT, 1)],
                            $number
                        );
                    }
                }

                $this->globalCallback = null;

                return all($this->found)->wait();
            });
        }

        return $this->globalCallback
            ->then(function (array $experiments) use ($experiment) {
                return $experiments[$experiment['number']];
            });
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            LabsExperiment::class === $type
            ||
            Model::class === $type && 'labs-experiment' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param LabsExperiment $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'number' => $object->getNumber(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format('Y-m-d\TH:i:s\Z'),
            'image' => [
                'thumbnail' => $this->normalizer->normalize($object->getThumbnail(), $format, $context),
            ],
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'labs-experiment';
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (empty($context['snippet'])) {
            $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);

            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof LabsExperiment;
    }
}
