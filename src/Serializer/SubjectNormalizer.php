<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Image;
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

final class SubjectNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $apiSdk;
    private $found = [];
    private $globalCallback;

    public function __construct(ApiSdk $apiSdk)
    {
        $this->apiSdk = $apiSdk;
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Subject
    {
        if (!empty($context['snippet'])) {
            $subject = $this->denormalizeSnippet($data);

            $data['impactStatement'] = $subject->then(function (Subject $subject) {
                return $subject->getImpactStatement();
            });
            $data['image'] = $subject->then(function (Subject $subject) {
                return $subject->getImage();
            });
        } else {
            $data['impactStatement'] = promise_for($data['impactStatement'] ?? null);
            $data['image'] = promise_for($data['image'])->then(function (array $image) use ($format, $context) {
                return $this->denormalizer->denormalize($image, Image::class, $format, $context);
            });
        }

        return new Subject(
            $data['id'],
            $data['name'],
            $data['impactStatement'],
            $data['image']
        );
    }

    private function denormalizeSnippet(array $subject) : PromiseInterface
    {
        if (isset($this->found[$subject['id']])) {
            return $this->found[$subject['id']];
        }

        $this->found[$subject['id']] = null;

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                foreach ($this->found as $id => $subject) {
                    if (null === $subject) {
                        $this->found[$id] = $this->apiSdk->subjects()->get($id);
                    }
                }

                $this->globalCallback = null;

                return all($this->found)->wait();
            });
        }

        return $this->globalCallback
            ->then(function (array $subjects) use ($subject) {
                return $subjects[$subject['id']];
            });
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
            $data['image'] = $this->normalizer->normalize($object->getImage(), $format, $context);

            if ($object->getImpactStatement()) {
                $data['impactStatement'] = $object->getImpactStatement();
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Subject;
    }
}
