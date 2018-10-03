<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\PeopleClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Client\People;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PersonResearch;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class PersonNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(PeopleClient $peopleClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $person) : string {
                return $person['id'];
            },
            function (string $id) use ($peopleClient) : PromiseInterface {
                return $peopleClient->getPerson(
                    ['Accept' => new MediaType(PeopleClient::TYPE_PERSON, People::VERSION_PERSON)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Person
    {
        if (!empty($context['snippet'])) {
            $person = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['affiliations'] = new PromiseSequence($person
                ->then(function (Result $person) {
                    return $person['affiliations'] ?? [];
                }));

            $data['competingInterests'] = $person
                ->then(function (Result $person) {
                    return $person['competingInterests'] ?? null;
                });

            $data['profile'] = new PromiseSequence($person
                ->then(function (Result $person) {
                    return $person['profile'] ?? [];
                }));

            $data['research'] = $person
                ->then(function (Result $person) {
                    return $person['research'] ?? [];
                });
        } else {
            $data['affiliations'] = new ArraySequence($data['affiliations'] ?? []);

            $data['competingInterests'] = promise_for($data['competingInterests'] ?? null);

            $data['profile'] = new ArraySequence($data['profile'] ?? []);

            $data['research'] = promise_for($data['research'] ?? []);
        }

        $data['affiliations'] = $data['affiliations']->map(function (array $place) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($place, Place::class, $format, $context);
        });

        if (isset($data['image'])) {
            $data['image'] = $this->denormalizer->denormalize($data['image'], Image::class, $format, $context);
        }

        $data['profile'] = $data['profile']->map(function (array $block) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['research'] = $data['research']
            ->then(function (array $research = null) use ($format, $context) {
                if (empty($research)) {
                    return null;
                }

                return new PersonResearch(
                    new ArraySequence(array_map(function (array $subject) use ($format, $context) {
                        $context['snippet'] = true;

                        return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
                    }, $research['expertises'] ?? [])),
                    $research['focuses'] ?? [],
                    $research['organisms'] ?? []
                );
            });

        return new Person(
            $data['id'],
            $this->denormalizer->denormalize($data, PersonDetails::class, $format, $context),
            $data['type']['id'],
            $data['type']['label'],
            $data['image'] ?? null,
            $data['affiliations'],
            $data['research'],
            $data['profile'],
            $data['competingInterests']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Person::class === $type;
    }

    /**
     * @param Person $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = $this->normalizer->normalize($object->getDetails(), $format, $context);

        $data['id'] = $object->getId();
        $data['type'] = [
            'id' => $object->getType(),
            'label' => $object->getTypeLabel(),
        ];

        if ($object->getThumbnail()) {
            $data['image'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        }

        if (empty($context['snippet'])) {
            if ($object->getAffiliations()->notEmpty()) {
                $data['affiliations'] = $object->getAffiliations()->map(function (Place $place) use ($format, $context) {
                    return $this->normalizer->normalize($place, $format, $context);
                })->toArray();
            }

            if ($object->getResearch()) {
                if (!$object->getResearch()->getExpertises()->isEmpty()) {
                    $data['research']['expertises'] = $object->getResearch()->getExpertises()
                        ->map(function (Subject $subject) use ($format, $context) {
                            $context['snippet'] = true;

                            return $this->normalizer->normalize($subject, $format, $context);
                        })->toArray();
                }
                if ($object->getResearch()->getFocuses()) {
                    $data['research']['focuses'] = $object->getResearch()->getFocuses();
                }
                if ($object->getResearch()->getOrganisms()) {
                    $data['research']['organisms'] = $object->getResearch()->getOrganisms();
                }
            }

            if (!$object->getProfile()->isEmpty()) {
                $data['profile'] = $object->getProfile()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if ($object->getCompetingInterests()) {
                $data['competingInterests'] = $object->getCompetingInterests();
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Person;
    }
}
