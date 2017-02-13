<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Highlights;
use eLife\ApiSdk\Model\Highlight;
use test\eLife\ApiSdk\ApiTestCase;

final class HighlightsTest extends ApiTestCase
{
    /** @var Highlights */
    private $highlights;

    /**
     * @before
     */
    protected function setUpSearch()
    {
        $this->highlights = (new ApiSdk($this->getHttpClient()))->highlights();
    }

    /**
     * @test
     */
    public function it_gets_a_list()
    {
        $this->mockHighlightsCall('foo', 10);

        $this->assertSame(10, $this->traverseAndSanityCheck($this->highlights->get('foo')));
    }

    private function traverseAndSanityCheck($search)
    {
        $count = 0;
        foreach ($search as $i => $model) {
            $this->assertInstanceOf(Highlight::class, $model);
            ++$count;
        }

        return $count;
    }
}
