<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\ProfilesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Profiles;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Profile;
use test\eLife\ApiSdk\ApiTestCase;

final class ProfilesTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Profiles */
    private $profiles;

    /**
     * @before
     */
    protected function setUpProfiles()
    {
        $this->profiles = (new ApiSdk($this->getHttpClient()))->profiles();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->profiles);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockProfileListCall(1, 1, 200);
        $this->mockProfileListCall(1, 100, 200);
        $this->mockProfileListCall(2, 100, 200);

        foreach ($this->profiles as $i => $profile) {
            $this->assertInstanceOf(Profile::class, $profile);
            $this->assertSame('profile'.$i, $profile->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockProfileListCall(1, 1, 10);

        $this->assertFalse($this->profiles->isEmpty());
        $this->assertSame(10, $this->profiles->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockProfileListCall(1, 1, 10);
        $this->mockProfileListCall(1, 100, 10);

        $array = $this->profiles->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $profile) {
            $this->assertInstanceOf(Profile::class, $profile);
            $this->assertSame('profile'.($i + 1), $profile->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockProfileListCall(1, 1, 1);

        $this->assertTrue(isset($this->profiles[0]));
        $this->assertSame('profile1', $this->profiles[0]->getId());

        $this->mockNotFound(
            'profiles?page=6&per-page=1&order=desc',
            ['Accept' => (string) new MediaType(ProfilesClient::TYPE_PROFILE_LIST, 1)]
        );

        $this->assertFalse(isset($this->profiles[5]));
        $this->assertSame(null, $this->profiles[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->profiles[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_profile()
    {
        $this->mockProfileCall(7, true);

        $profile = $this->profiles->get('profile7')->wait();

        $this->assertInstanceOf(Profile::class, $profile);
        $this->assertSame('profile7', $profile->getId());
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockProfileListCall(1, 1, 5);
        $this->mockProfileListCall(1, 100, 5);

        $values = $this->profiles->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'profile1', 'profile2', 'profile3', 'profile4', 'profile5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockProfileListCall(1, 1, 5);
        $this->mockProfileListCall(1, 100, 5);

        $values = $this->profiles->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['profile1', 'profile2', 'profile3', 'profile4', 'profile5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockProfileListCall(1, 1, 5);
        $this->mockProfileListCall(1, 100, 5);

        $values = $this->profiles->drop(2)->map($this->tidyValue());

        $this->assertSame(['profile1', 'profile2', 'profile4', 'profile5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockProfileListCall(1, 1, 5);
        $this->mockProfileListCall(1, 100, 5);

        $values = $this->profiles->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['profile1', 'profile2', 2, 'profile3', 'profile4', 'profile5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockProfileListCall(1, 1, 5);
        $this->mockProfileListCall(1, 100, 5);

        $values = $this->profiles->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['profile1', 'profile2', 2, 'profile4', 'profile5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockProfileListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->profiles->slice($offset, $length) as $i => $profile) {
            $this->assertInstanceOf(Profile::class, $profile);
            $this->assertSame('profile'.($expected[$i]), $profile->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockProfileListCall(1, 1, 3);
        $this->mockProfileListCall(1, 100, 3);

        $map = function (Profile $profile) {
            return $profile->getId();
        };

        $this->assertSame(['profile1', 'profile2', 'profile3'], $this->profiles->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockProfileListCall(1, 1, 5);
        $this->mockProfileListCall(1, 100, 5);

        $filter = function (Profile $profile) {
            return substr($profile->getId(), -1) > 3;
        };

        foreach ($this->profiles->filter($filter) as $i => $profile) {
            $this->assertSame('profile'.($i + 4), $profile->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockProfileListCall(1, 1, 5);
        $this->mockProfileListCall(1, 100, 5);

        $reduce = function (int $carry = null, Profile $profile) {
            return $carry + substr($profile->getId(), -1);
        };

        $this->assertSame(115, $this->profiles->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->profiles, $this->profiles->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockProfileListCall(1, 1, 5);
        $this->mockProfileListCall(1, 100, 5);

        $sort = function (Profile $a, Profile $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->profiles->sort($sort) as $i => $profile) {
            $this->assertSame('profile'.(5 - $i), $profile->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockProfileListCall(1, 1, 5, false);
        $this->mockProfileListCall(1, 100, 5, false);

        foreach ($this->profiles->reverse() as $i => $profile) {
            $this->assertSame('profile'.$i, $profile->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockProfileListCall(1, 1, 10);

        $this->profiles->count();

        $this->assertSame(10, $this->profiles->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockProfileListCall(1, 1, 200);
        $this->mockProfileListCall(1, 100, 200);
        $this->mockProfileListCall(2, 100, 200);

        $this->profiles->toArray();

        $this->mockProfileListCall(1, 1, 200, false);
        $this->mockProfileListCall(1, 100, 200, false);
        $this->mockProfileListCall(2, 100, 200, false);

        $this->profiles->reverse()->toArray();
    }
}
