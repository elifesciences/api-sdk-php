<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Bioprotocols;
use eLife\ApiSdk\Model\Bioprotocol;
use eLife\ApiSdk\Model\Identifier;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class BioprotocolsTest extends ApiTestCase
{
    /** @var Bioprotocols */
    private $bioprotocols;

    /**
     * @before
     */
    protected function setUpBioprotocols()
    {
        $this->bioprotocols = (new ApiSdk($this->getHttpClient()))->bioprotocols();
    }

    /**
     * @test
     */
    public function it_is_an_array()
    {
        $this->mockBioprotocolsCall('article', '09560');

        $list = $this->bioprotocols->list(Identifier::article('09560'));

        $this->assertEquals(
            [
                's1-2-3' => Builder::for(Bioprotocol::class)
                    ->withSectionId('s1-2-3')
                    ->withStatus(false)
                    ->withUri('https://example.com/s1-2-3')
                    ->__invoke(),
                's2-3-4' => Builder::for(Bioprotocol::class)
                    ->withSectionId('s2-3-4')
                    ->withStatus(true)
                    ->withUri('https://example.com/s2-3-4')
                    ->__invoke(),
            ],
            $list->wait()
        );
    }
}
