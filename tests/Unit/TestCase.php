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


    public function setUp()
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

}
