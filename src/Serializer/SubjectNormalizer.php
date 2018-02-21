<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class SubjectNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(SubjectsClient $subjectsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $subject) : string {
                return $subject['id'];
            },
            function (string $id) use ($subjectsClient) : PromiseInterface {
                return $subjectsClient->getSubject(
                    ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Subject
    {
        if (!empty($context['snippet'])) {
            $subject = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['impactStatement'] = $subject->then(function (Result $subject) {
                return $subject['impactStatement'] ?? null;
            });
            $data['aimsAndScope'] = new PromiseSequence($subject
                ->then(function (Result $subject) {
                    return $subject['aimsAndScope'];
                }));
            $data['image'] = $subject->then(function (Result $subject) use ($format, $context) {
                return $subject['image'];
            });
        } else {
            $data['impactStatement'] = promise_for($data['impactStatement'] ?? null);
            $data['aimsAndScope'] = new ArraySequence($data['aimsAndScope'] ?? []);
            $data['image'] = promise_for($data['image']);
        }

        $data['aimsAndScope'] = $data['aimsAndScope']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, ['snippet' => false] + $context);
        });

        $banner = $data['image']->then(function (array $image) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($image['banner'], Image::class, $format, $context);
        });

        $thumbnail = $data['image']->then(function (array $image) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($image['thumbnail'], Image::class, $format, $context);
        });

        return new Subject(
            $data['id'],
            $data['name'],
            $data['impactStatement'],
            $data['aimsAndScope'],
            $banner,
            $thumbnail
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Subject::class === $type;
    }

    /**
     * @param Subject $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'name' => $object->getName(),
        ];

        if (empty($context['snippet'])) {
            $data['image'] = [
                'banner' => $this->normalizer->normalize($object->getBanner(), $format, $context),
                'thumbnail' => $this->normalizer->normalize($object->getThumbnail(), $format, $context),
            ];

            if ($object->getImpactStatement()) {
                $data['impactStatement'] = $object->getImpactStatement();
            }

            if (!$object->getAimsAndScope()->isEmpty()) {
                $data['aimsAndScope'] = $object->getAimsAndScope()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Subject;
    }
}
