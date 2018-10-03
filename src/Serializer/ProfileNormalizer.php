<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\ProfilesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Client\Profiles;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Profile;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProfileNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(ProfilesClient $profilesClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $profile) : string {
                return $profile['id'];
            },
            function (string $id) use ($profilesClient) : PromiseInterface {
                return $profilesClient->getProfile(
                    ['Accept' => new MediaType(ProfilesClient::TYPE_PROFILE, Profiles::VERSION_PROFILE)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Profile
    {
        if (!empty($context['snippet'])) {
            $profile = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['affiliations'] = new PromiseSequence($profile
                ->then(function (Result $profile) {
                    return $profile['affiliations'] ?? [];
                }));

            $data['emailAddresses'] = new PromiseSequence($profile
                ->then(function (Result $profile) {
                    return $profile['emailAddresses'] ?? [];
                }));
        } else {
            $data['affiliations'] = new ArraySequence($data['affiliations'] ?? []);

            $data['emailAddresses'] = new ArraySequence($data['emailAddresses'] ?? []);
        }

        $data['affiliations'] = $data['affiliations']->map(function (array $accessControl) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($accessControl, AccessControl::class, $format, $context + ['class' => Place::class]);
        });

        $data['emailAddresses'] = $data['emailAddresses']->map(function (array $accessControl) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($accessControl, AccessControl::class, $format, $context);
        });

        return new Profile(
            $data['id'],
            $this->denormalizer->denormalize($data, PersonDetails::class, $format, $context),
            $data['affiliations'],
            $data['emailAddresses']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Profile::class === $type;
    }

    /**
     * @param Profile $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = $this->normalizer->normalize($object->getDetails(), $format, $context);

        $data['id'] = $object->getId();

        if (empty($context['snippet'])) {
            if ($object->getAffiliations()->notEmpty()) {
                $data['affiliations'] = $object->getAffiliations()->map(function (AccessControl $accessControl) use ($format, $context) {
                    return $this->normalizer->normalize($accessControl, $format, $context);
                })->toArray();
            }
            if ($object->getEmailAddresses()->notEmpty()) {
                $data['emailAddresses'] = $object->getEmailAddresses()->map(function (AccessControl $accessControl) use ($format, $context) {
                    return $this->normalizer->normalize($accessControl, $format, $context);
                })->toArray();
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Profile;
    }
}
