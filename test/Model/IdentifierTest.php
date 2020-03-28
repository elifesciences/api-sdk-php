<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Identifier;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Traversable;

final class IdentifierTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider identifierProvider
     */
    public function it_has_a_type(string $method, string $type, string $id)
    {
        $identifier = Identifier::{$method}($id);

        $this->assertSame($type, $identifier->getType());
    }

    /**
     * @test
     * @dataProvider identifierProvider
     */
    public function it_has_an_id(string $method, string $type, string $id)
    {
        $identifier = Identifier::{$method}($id);

        $this->assertSame((string) $id, $identifier->getId());
    }

    /**
     * @test
     * @dataProvider identifierProvider
     */
    public function it_can_be_created_from_a_string(string $method, string $type, string $id)
    {
        $identifier = Identifier::fromString("$type/$id");

        $this->assertSame($type, $identifier->getType());
        $this->assertSame((string) $id, $identifier->getId());
    }

    public function identifierProvider() : Traversable
    {
        $examples = [
            'annual-report' => 2017,
            'article' => '1234-5678',
            'blog-article' => '1234-5678',
            'collection' => '1234-5678',
            'event' => '1234-5678',
            'interview' => '1234-5678',
            'labs-post' => '1234-5678',
            'person' => '1234-5678',
            'podcast-episode' => 7,
            'press-package' => '1234-5678',
            'profile' => '1234-5678',
            'regional-collection' => '1234-5678',
            'subject' => '1234-5678',
        ];

        foreach ($examples as $type => $id) {
            yield str_replace('-', ' ', $type) => [
                lcfirst(str_replace('-', '', ucwords($type, '-'))),
                $type,
                $id,
            ];
        }
    }

    /**
     * @test
     * @dataProvider invalidStringProvider
     */
    public function it_cannot_be_created_from_invalid_strings(string $input)
    {
        $this->expectException(InvalidArgumentException::class);

        Identifier::fromString($input);
    }

    public function invalidStringProvider() : Traversable
    {
        yield 'empty string' => [''];
        yield 'plain string' => ['foo'];
        yield 'invalid type' => ['art-cle:1234-5678'];
        yield 'missing type' => [':1234-5678'];
        yield 'invalid id' => ['article:1234+5678'];
        yield 'missing id' => ['article:'];
        yield 'space' => [' article:1234-5678'];
    }
}
