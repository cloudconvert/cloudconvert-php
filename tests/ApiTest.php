<?php
namespace CloudConvert\Tests;

use CloudConvert\Api;
use CloudConvert\Exceptions\ApiTemporaryUnavailableException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;


/**
 * Tests of Api class
 *
 * @package CloudConvert
 * @category CloudConvert
 * @author Josias Montag <josias@montag.info>
 */
class ApiTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Define id to create object
     */
    protected function setUp()
    {
        $this->api_key = "tests";
    }

    /**
     * Get private and protected method to unit test it
     */
    protected static function getPrivateMethod($name)
    {
        $class = new \ReflectionClass('CloudConvert\Api');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    protected static function getPrivateProperty($name)
    {
        $class = new \ReflectionClass('CloudConvert\Api');
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * Test if request without authentication works
     */
    public function testIfRequestWithoutAuthenticationWorks()
    {

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "[{\"inputformat\":\"pdf\",\"outputformat\":\"pdf\",\"converter\":\"test\",\"converteroptions\":{\"test_option\":true}}]"),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $api = new Api($this->api_key, $client);

        $invoker = self::getPrivateMethod('rawCall');
        $result = $invoker->invokeArgs($api, array(
            'GET',
            '/conversiontypes',
            array(
                'inputformat' => 'pdf',
                'outputformat' => 'pdf',
            ),
            false,
        ));
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals("test",$result[0]['converter']);
    }


    /**
     * Test if request with authentication works
     */
    public function testIfRequestWithAuthenticationWorks()
    {

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"url\":\"//processurl\"}"),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $api = new Api($this->api_key, $client);

        $invoker = self::getPrivateMethod('rawCall');
        $result = $invoker->invokeArgs($api, array(
            'POST',
            '/process',
            array(
                'inputformat' => 'pdf',
                'outputformat' => 'pdf',
            ),
            true,
        ));

        $this->assertEquals("//processurl",$result['url']);
        $last = $mock->getLastRequest();

        $this->assertEquals('Bearer ' . $this->api_key, $last->getHeaderLine('Authorization'));

    }


    /**
     * Test if Process creation works
     */
    public function testIfProcessCreationWorks()
    {

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"url\":\"//processurl\"}"),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $api = new Api($this->api_key, $client);

        $process = $api->createProcess(array(
            'inputformat' => 'pdf',
            'outputformat' => 'pdf',
        ));
        $this->assertInstanceOf('CloudConvert\Process', $process);
        $this->assertEquals("//processurl", $process->url);
    }


    /**
     * Test if Process creation with invalid format throws a CloudConvert\Exceptions\ApiException
     */
    public function testIfProcessCreationWithInvalidFormatThrowsTheRightException()
    {

        $mock = new MockHandler([
            new Response(400, ['Content-Type' => 'application/json; charset=utf-8'], "{\"message\":\"This conversiontype is not supported!\"}"),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);


        $api = new Api($this->api_key, $client);

        $this->setExpectedException('CloudConvert\Exceptions\ApiBadRequestException', 'This conversiontype is not supported!', 400);

        $api->createProcess(array(
            'inputformat' => 'invalid',
            'outputformat' => 'pdf',
        ));
    }


    /**
     * Test if API error 503 throws a CloudConvert\Exceptions\ApiTemporaryUnavailableException with correct retryAfter value
     */
    public function testIfApiTemporaryUnavailableExceptionIsThrown()
    {

        $mock = new MockHandler([
            new Response(503, ['Retry-After' => 30, 'Content-Type' => 'application/json; charset=utf-8'], "{\"message\":\"API unavailable. Please try later.\"}"),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);


        $api = new Api($this->api_key, $client);

        $invoker = self::getPrivateMethod('rawCall');

        try {
            $invoker->invokeArgs($api, array(
                'GET',
                '/conversiontypes',
                array(
                    'inputformat' => 'pdf',
                    'outputformat' => 'pdf',
                ),
                false,
            ));
        }
        catch (ApiTemporaryUnavailableException $expected) {
            $this->assertEquals(30, $expected->retryAfter);
            $this->assertEquals("API unavailable. Please try later.", $expected->getMessage());
            return;
        }

        $this->fail('CloudConvert\Exceptions\ApiTemporaryUnavailableException has not been raised.');

    }


}
