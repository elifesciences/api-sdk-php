<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Serializer\SnippetDenormalizer;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;

final class SnippetDenormalizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_denormalizes_snippets()
    {
        $settled = 0;

        $snippetDenormalizer = new SnippetDenormalizer(
            function (array $item) : int {
                return $item['id'];
            },
            function (int $id) use (&$settled) : PromiseInterface {
                return promise_for(['id' => $id, 'name' => 'Item '.$id])
                    ->then(function (array $item) use (&$settled) {
                        ++$settled;

                        return $item;
                    });
            }
        );

        $item1 = $snippetDenormalizer->denormalizeSnippet(['id' => 1]);
        $item2 = $snippetDenormalizer->denormalizeSnippet(['id' => 2]);

        $this->assertSame(0, $settled);

        $this->assertSame(['id' => 1, 'name' => 'Item 1'], $item1->wait());

        $this->assertSame(2, $settled);

        $this->assertSame(['id' => 2, 'name' => 'Item 2'], $item2->wait());
    }
}
