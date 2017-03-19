<?php
namespace CloudConvert\Tests\Integration;

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
class ApiIntegrationTest extends \PHPUnit_Framework_TestCase
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


}
