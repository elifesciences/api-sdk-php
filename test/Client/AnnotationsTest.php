<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\AnnotationsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Annotations;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Annotation;
use test\eLife\ApiSdk\ApiTestCase;

final class AnnotationsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Annotations */
    private $annotations;

    /**
     * @before
     */
    protected function setUpAnnotationss()
    {
        $this->annotations = (new ApiSdk($this->getHttpClient()))->annotations();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $list = $this->annotations->list('username');
        $this->assertInstanceOf(Sequence::class, $list);
    }

    /**
     * @test
     */
    public function it_has_an_access_level()
    {
        $list = $this->annotations->list('username', 'restricted');

        $this->mockAnnotationListCall('username', 1, 1, 1, true, 'updated', 'restricted');

        $this->assertFalse($list->isEmpty());
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 200);
        $this->mockAnnotationListCall('username', 1, 100, 200);
        $this->mockAnnotationListCall('username', 2, 100, 200);

        foreach ($list as $i => $annotation) {
            $this->assertInstanceOf(Annotation::class, $annotation);
            $this->assertSame('annotation-'.$i, $annotation->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 10);

        $this->assertFalse($list->isEmpty());
        $this->assertSame(10, $list->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 10);
        $this->mockAnnotationListCall('username', 1, 100, 10);

        $array = $list->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $annotation) {
            $this->assertInstanceOf(Annotation::class, $annotation);
            $this->assertSame('annotation-'.($i + 1), $annotation->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 1);

        $this->assertTrue(isset($list[0]));
        $this->assertSame('annotation-1', $list[0]->getId());

        $this->mockNotFound(
            'annotations?by=username&page=6&per-page=1&order=desc&use-date=updated&access=public',
            ['Accept' => new MediaType(AnnotationsClient::TYPE_ANNOTATION_LIST, 1)]
        );

        $this->assertFalse(isset($list[5]));
        $this->assertSame(null, $list[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $list = $this->annotations->list('username');

        $this->expectException(BadMethodCallException::class);

        $list[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5);
        $this->mockAnnotationListCall('username', 1, 100, 5);

        $values = $list->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'annotation-1', 'annotation-2', 'annotation-3', 'annotation-4', 'annotation-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5);
        $this->mockAnnotationListCall('username', 1, 100, 5);

        $values = $list->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['annotation-1', 'annotation-2', 'annotation-3', 'annotation-4', 'annotation-5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5);
        $this->mockAnnotationListCall('username', 1, 100, 5);

        $values = $list->drop(2)->map($this->tidyValue());

        $this->assertSame(['annotation-1', 'annotation-2', 'annotation-4', 'annotation-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5);
        $this->mockAnnotationListCall('username', 1, 100, 5);

        $values = $list->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['annotation-1', 'annotation-2', 2, 'annotation-3', 'annotation-4', 'annotation-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5);
        $this->mockAnnotationListCall('username', 1, 100, 5);

        $values = $list->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['annotation-1', 'annotation-2', 2, 'annotation-4', 'annotation-5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        $list = $this->annotations->list('username');

        foreach ($calls as $call) {
            $this->mockAnnotationListCall('username', $call['page'], $call['per-page'], 5);
        }

        foreach ($list->slice($offset, $length) as $i => $annotation) {
            $this->assertInstanceOf(Annotation::class, $annotation);
            $this->assertSame('annotation-'.($expected[$i]), $annotation->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 3);
        $this->mockAnnotationListCall('username', 1, 100, 3);

        $map = function (Annotation $annotation) {
            return $annotation->getId();
        };

        $this->assertSame(['annotation-1', 'annotation-2', 'annotation-3'], $list->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5);
        $this->mockAnnotationListCall('username', 1, 100, 5);

        $filter = function (Annotation $annotation) {
            return substr($annotation->getId(), -1) > 3;
        };

        foreach ($list->filter($filter) as $i => $annotation) {
            $this->assertSame('annotation-'.($i + 4), $annotation->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5);
        $this->mockAnnotationListCall('username', 1, 100, 5);

        $reduce = function (int $carry = null, Annotation $annotation) {
            return $carry + substr($annotation->getId(), -1);
        };

        $this->assertSame(115, $list->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $list = $this->annotations->list('username');

        $this->assertSame($list, $list->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5);
        $this->mockAnnotationListCall('username', 1, 100, 5);

        $sort = function (Annotation $a, Annotation $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($list->sort($sort) as $i => $annotation) {
            $this->assertSame('annotation-'.(5 - $i), $annotation->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 5, false);
        $this->mockAnnotationListCall('username', 1, 100, 5, false);

        foreach ($list->reverse() as $i => $annotation) {
            $this->assertSame('annotation-'.$i, $annotation->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 10);

        $list->count();

        $this->assertSame(10, $list->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $list = $this->annotations->list('username');

        $this->mockAnnotationListCall('username', 1, 1, 200);
        $this->mockAnnotationListCall('username', 1, 100, 200);
        $this->mockAnnotationListCall('username', 2, 100, 200);

        $list->toArray();

        $this->mockAnnotationListCall('username', 1, 1, 200, false);
        $this->mockAnnotationListCall('username', 1, 100, 200, false);
        $this->mockAnnotationListCall('username', 2, 100, 200, false);

        $list->reverse()->toArray();
    }
}
