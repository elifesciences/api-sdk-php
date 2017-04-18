<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\PressPackagesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\PressPackages;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\PressPackage;
use test\eLife\ApiSdk\ApiTestCase;

final class PressPackagesTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var PressPackages */
    private $pressPackages;

    /**
     * @before
     */
    protected function setUpPressPackages()
    {
        $this->pressPackages = (new ApiSdk($this->getHttpClient()))->pressPackages();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->pressPackages);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockPressPackagesListCall(1, 1, 200);
        $this->mockPressPackagesListCall(1, 100, 200);
        $this->mockPressPackagesListCall(2, 100, 200);

        foreach ($this->pressPackages as $i => $pressPackage) {
            $this->assertInstanceOf(PressPackage::class, $pressPackage);
            $this->assertSame('press-package-'.$i, $pressPackage->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockPressPackagesListCall(1, 1, 10);

        $this->assertFalse($this->pressPackages->isEmpty());
        $this->assertSame(10, $this->pressPackages->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockPressPackagesListCall(1, 1, 10);
        $this->mockPressPackagesListCall(1, 100, 10);

        $array = $this->pressPackages->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $pressPackage) {
            $this->assertInstanceOf(PressPackage::class, $pressPackage);
            $this->assertSame('press-package-'.($i + 1), $pressPackage->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockPressPackagesListCall(1, 1, 1);

        $this->assertTrue(isset($this->pressPackages[0]));
        $this->assertSame('press-package-1', $this->pressPackages[0]->getId());

        $this->mockNotFound(
            'press-packages?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE_LIST, 1)]
        );

        $this->assertFalse(isset($this->pressPackages[5]));
        $this->assertSame(null, $this->pressPackages[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->pressPackages[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_press_package()
    {
        $this->mockPressPackageCall(7, true);

        $pressPackage = $this->pressPackages->get('press-package-7')->wait();

        $this->assertInstanceOf(PressPackage::class, $pressPackage);
        $this->assertSame('press-package-7', $pressPackage->getId());

        $this->assertInstanceOf(Paragraph::class, $pressPackage->getContent()[0]);
        $this->assertSame('Press package press-package-7 text', $pressPackage->getContent()[0]->getText());
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockPressPackagesListCall(1, 1, 5);
        $this->mockPressPackagesListCall(1, 100, 5);

        $values = $this->pressPackages->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'press-package-1', 'press-package-2', 'press-package-3', 'press-package-4', 'press-package-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockPressPackagesListCall(1, 1, 5);
        $this->mockPressPackagesListCall(1, 100, 5);

        $values = $this->pressPackages->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['press-package-1', 'press-package-2', 'press-package-3', 'press-package-4', 'press-package-5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockPressPackagesListCall(1, 1, 5);
        $this->mockPressPackagesListCall(1, 100, 5);

        $values = $this->pressPackages->drop(2)->map($this->tidyValue());

        $this->assertSame(['press-package-1', 'press-package-2', 'press-package-4', 'press-package-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockPressPackagesListCall(1, 1, 5);
        $this->mockPressPackagesListCall(1, 100, 5);

        $values = $this->pressPackages->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['press-package-1', 'press-package-2', 2, 'press-package-3', 'press-package-4', 'press-package-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockPressPackagesListCall(1, 1, 5);
        $this->mockPressPackagesListCall(1, 100, 5);

        $values = $this->pressPackages->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['press-package-1', 'press-package-2', 2, 'press-package-4', 'press-package-5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockPressPackagesListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->pressPackages->slice($offset, $length) as $i => $pressPackage) {
            $this->assertInstanceOf(PressPackage::class, $pressPackage);
            $this->assertSame('press-package-'.($expected[$i]), $pressPackage->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockPressPackagesListCall(1, 1, 3);
        $this->mockPressPackagesListCall(1, 100, 3);

        $map = function (PressPackage $pressPackage) {
            return $pressPackage->getId();
        };

        $this->assertSame(['press-package-1', 'press-package-2', 'press-package-3'], $this->pressPackages->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockPressPackagesListCall(1, 1, 5);
        $this->mockPressPackagesListCall(1, 100, 5);

        $filter = function (PressPackage $pressPackage) {
            return substr($pressPackage->getId(), -1) > 3;
        };

        foreach ($this->pressPackages->filter($filter) as $i => $pressPackage) {
            $this->assertSame('press-package-'.($i + 4), $pressPackage->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockPressPackagesListCall(1, 1, 5);
        $this->mockPressPackagesListCall(1, 100, 5);

        $reduce = function (int $carry = null, PressPackage $pressPackage) {
            return $carry + substr($pressPackage->getId(), -1);
        };

        $this->assertSame(115, $this->pressPackages->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->pressPackages, $this->pressPackages->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockPressPackagesListCall(1, 1, 5);
        $this->mockPressPackagesListCall(1, 100, 5);

        $sort = function (PressPackage $a, PressPackage $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->pressPackages->sort($sort) as $i => $pressPackage) {
            $this->assertSame('press-package-'.(5 - $i), $pressPackage->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockPressPackagesListCall(1, 1, 5, false);
        $this->mockPressPackagesListCall(1, 100, 5, false);

        foreach ($this->pressPackages->reverse() as $i => $pressPackage) {
            $this->assertSame('press-package-'.$i, $pressPackage->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockPressPackagesListCall(1, 1, 10);

        $this->pressPackages->count();

        $this->assertSame(10, $this->pressPackages->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockPressPackagesListCall(1, 1, 200);
        $this->mockPressPackagesListCall(1, 100, 200);
        $this->mockPressPackagesListCall(2, 100, 200);

        $this->pressPackages->toArray();

        $this->mockPressPackagesListCall(1, 1, 200, false);
        $this->mockPressPackagesListCall(1, 100, 200, false);
        $this->mockPressPackagesListCall(2, 100, 200, false);

        $this->pressPackages->reverse()->toArray();
    }
}
