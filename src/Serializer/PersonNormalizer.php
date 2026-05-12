<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiClient\PeopleClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Client\People;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PersonResearch;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class PersonNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(PeopleClient $peopleClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $person) : string {
                return $person['id'];
            },
            function (string $id) use ($peopleClient) : PromiseInterface {
                return $peopleClient->getPerson(
                    ['Accept' => (string) new MediaType(PeopleClient::TYPE_PERSON, People::VERSION_PERSON)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : Person
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

            $data['emailAddresses'] = new PromiseSequence($person
                ->then(function (Result $profile) {
                    return $profile['emailAddresses'] ?? [];
                }));

            $data['name']['givenNames'] = $person
                ->then(function (Result $person) {
                    return $person['name']['givenNames'] ?? null;
                });

            $data['name']['surname'] = $person
                ->then(function (Result $person) {
                    return $person['name']['surname'] ?? null;
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

            $data['emailAddresses'] = new ArraySequence($data['emailAddresses'] ?? []);

            $data['name']['givenNames'] = promise_for($data['name']['givenNames'] ?? null);

            $data['name']['surname'] = promise_for($data['name']['surname'] ?? null);

            $data['profile'] = new ArraySequence($data['profile'] ?? []);

            $data['research'] = promise_for($data['research'] ?? []);
        }

        $data['affiliations'] = $data['affiliations']->map(function (array $place) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($place, Place::class, $format, $context);
        });

        $data['emailAddresses'] = $data['emailAddresses']->map(function (array $accessControl) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($accessControl, AccessControl::class, $format, $context);
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
            $data['name']['givenNames'],
            $data['name']['surname'],
            $data['type']['id'],
            $data['type']['label'],
            $data['image'] ?? null,
            $data['affiliations'],
            $data['research'],
            $data['profile'],
            $data['competingInterests'],
            $data['emailAddresses']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Person::class === $type;
    }

    /**
     * @param Person $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = $this->normalizer->normalize($data->getDetails(), $format, $context);

        $arr['id'] = $data->getId();
        $arr['type'] = [
            'id' => $data->getType(),
            'label' => $data->getTypeLabel(),
        ];

        if ($data->getThumbnail()) {
            $arr['image'] = $this->normalizer->normalize($data->getThumbnail(), $format, $context);
        }

        if (empty($context['snippet'])) {
            if ($data->getGivenNames()) {
                $arr['name']['givenNames'] = $data->getGivenNames();
            }

            if ($data->getSurname()) {
                $arr['name']['surname'] = $data->getSurname();
            }

            if ($data->getAffiliations()->notEmpty()) {
                $arr['affiliations'] = $data->getAffiliations()->map(function (Place $place) use ($format, $context) {
                    return $this->normalizer->normalize($place, $format, $context);
                })->toArray();
            }

            if ($data->getEmailAddresses()->notEmpty()) {
                $arr['emailAddresses'] = $data->getEmailAddresses()->map(function (AccessControl $accessControl) use ($format, $context) {
                    return $this->normalizer->normalize($accessControl, $format, $context);
                })->toArray();
            }

            if ($data->getResearch()) {
                if (!$data->getResearch()->getExpertises()->isEmpty()) {
                    $arr['research']['expertises'] = $data->getResearch()->getExpertises()
                        ->map(function (Subject $subject) use ($format, $context) {
                            $context['snippet'] = true;

                            return $this->normalizer->normalize($subject, $format, $context);
                        })->toArray();
                }
                if ($data->getResearch()->getFocuses()) {
                    $arr['research']['focuses'] = $data->getResearch()->getFocuses();
                }
                if ($data->getResearch()->getOrganisms()) {
                    $arr['research']['organisms'] = $data->getResearch()->getOrganisms();
                }
            }

            if (!$data->getProfile()->isEmpty()) {
                $arr['profile'] = $data->getProfile()
                    ->map(function (Block $block) use ($format, $context) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray();
            }

            if ($data->getCompetingInterests()) {
                $arr['competingInterests'] = $data->getCompetingInterests();
            }
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Person;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Person::class => true,
        ];
    }
}
