<?php


namespace CloudConvert\Tests\Unit;

use CloudConvert\CloudConvert;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @var CloudConvert
     */
    protected $cloudConvert;

    /**
     * @var Client
     */
    protected $mockClient;


    public function setUp(): void
    {

        $this->cloudConvert = new CloudConvert([
            'api_key'     => 'test_api_key',
            'http_client' => $this->getMockClient()
        ]);

        parent::setUp();
    }

    protected function getMockClient(): Client
    {

        if ($this->mockClient === null) {
            $this->mockClient = new Client();
            $this->mockClient->setDefaultResponse(new Response(404, [], ''));
        }

        return $this->mockClient;

    }

    /**
     * The method is here to provice BC compatibility with PHPUnit >= 9 where this method was removed.
     *
     * @param string $regex
     */
    public function expectExceptionMessageRegExp(string $regex): void
    {
        if (!method_exists($this, 'expectExceptionMessageMatches')) {
            parent::expectExceptionMessageRegExp($regex);

            return;
        }

        $this->expectExceptionMessageMatches($regex);
    }

}
