<?php
namespace CloudConvert\tests;

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
        $this->api_key = getenv('API_KEY');
        $this->api = new Api($this->api_key);
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
        $invoker = self::getPrivateMethod('rawCall');
        $result = $invoker->invokeArgs($this->api, array(
            'GET',
            '/conversiontypes',
            array(
                'inputformat' => 'pdf',
                'outputformat' => 'pdf',
            ),
            false,
        ));
        $this->assertNotEmpty($result);
    }
    /**
     * Test if request without authentication works
     */
    public function testIfRequestWithAuthenticationWorks()
    {
        $invoker = self::getPrivateMethod('rawCall');
        $result = $invoker->invokeArgs($this->api, array(
            'POST',
            '/process',
            array(
                'inputformat' => 'pdf',
                'outputformat' => 'pdf',
            ),
            true,
        ));

        $this->assertArrayHasKey('url', $result);
    }
    /**
     * Test if Process creation works
     */
    public function testIfProcessCreationWorks()
    {
        $process = $this->api->createProcess(array(
            'inputformat' => 'pdf',
            'outputformat' => 'pdf',
        ));
        $this->assertInstanceOf('CloudConvert\Process', $process);
    }
    /**
     * Test if Process creation with invalid format throws a CloudConvert\Exceptions\ApiException
     */
    public function testIfProcessCreationWithInvalidFormatThrowsTheRightException()
    {
        $this->setExpectedException('CloudConvert\Exceptions\ApiException', 'This conversiontype is not supported!', 400);
        $this->setExpectedException('CloudConvert\Exceptions\ApiBadRequestException', 'This conversiontype is not supported!', 400);

        $this->api->createProcess(array(
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
            return;
        }

        $this->fail('CloudConvert\Exceptions\ApiTemporaryUnavailableException has not been raised.');

    }
}
