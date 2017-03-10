<?php

namespace test\eLife\ApiSdk;

use Eris\TestTrait;
use Eris\Generator;

class LoadingArticlesTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;

    public function testLoadingArticlesInBatches()
    {
        $script = realpath(__DIR__ . '/../scripts/load_all_articles.php');
        $this
            ->forAll(
                Generator\nat(),
                Generator\choose(1, 100)
            )
            ->then(function ($offset, $limit) use ($script) {
                $limit++;
                $cli = "php $script $offset $limit";
                exec($cli, $output, $returnCode);
                $this->assertEquals(0, $returnCode, "CLI: $cli".PHP_EOL.PHP_EOL.implode(PHP_EOL, $output));
            });
    }
}
