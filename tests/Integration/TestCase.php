<?php


namespace CloudConvert\Tests\Integration;

use CloudConvert\CloudConvert;
use Http\Mock\Client;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @var CloudConvert
     */
    protected $cloudConvert;


    public function setUp(): void
    {

        $this->cloudConvert = new CloudConvert([
            'sandbox' => true,
            'api_key' => getenv('CLOUDCONVERT_API_KEY')
        ]);

        parent::setUp();
    }


}
