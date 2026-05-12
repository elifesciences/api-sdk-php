<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class SubjectNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(SubjectsClient $subjectsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $subject) : string {
                return $subject['id'];
            },
            function (string $id) use ($subjectsClient) : PromiseInterface {
                return $subjectsClient->getSubject(
                    ['Accept' => (string) new MediaType(SubjectsClient::TYPE_SUBJECT, Subjects::VERSION_SUBJECT)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : Subject
    {
        if (!empty($context['snippet'])) {
            $subject = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['impactStatement'] = $subject->then(function (Result $subject) {
                return $subject['impactStatement'] ?? null;
            });
            $data['aimsAndScope'] = new PromiseSequence($subject
                ->then(function (Result $subject) {
                    return $subject['aimsAndScope'] ?? [];
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

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Subject::class === $type;
    }

    /**
     * @param Subject $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'id' => $data->getId(),
            'name' => $data->getName(),
        ];

        if (empty($context['snippet'])) {
            $arr['image'] = [
                'banner' => $this->normalizer->normalize($data->getBanner(), $format, $context),
                'thumbnail' => $this->normalizer->normalize($data->getThumbnail(), $format, $context),
            ];

            if ($data->getImpactStatement()) {
                $arr['impactStatement'] = $data->getImpactStatement();
            }

            if ($data->getAimsAndScope()->notEmpty()) {
                $arr['aimsAndScope'] = $data->getAimsAndScope()
                    ->map(function (Block\Paragraph $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Subject;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Subject::class => true,
        ];
    }
}
