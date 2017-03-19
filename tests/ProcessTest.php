<?php
namespace CloudConvert\Tests;

use CloudConvert\Api;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Tests of Process class
 *
 * @package CloudConvert
 * @category CloudConvert
 * @author Josias Montag <josias@montag.info>
 */
class ProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Define id to create object
     */
    protected function setUp()
    {
        $this->api_key = "tests";

        $this->mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"url\":\"//processurl\"}"),
            new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"url\":\"//processurl\", \"step\":\"input\", \"upload\": {\"url\":\"//processurl/upload\"}}"),
        ]);

        $handler = HandlerStack::create($this->mock);
        $client = new Client(['handler' => $handler]);

        $this->api = new Api($this->api_key, $client);

    }



    /**
     * Test if  uploading an input file works
     */
    public function testIfUploadWorks()
    {
        $process = $this->api->createProcess([
            'inputformat' => 'png',
            'outputformat' => 'pdf',
        ]);
        $process->start([
            'input' => 'upload',
            'outputformat' => 'pdf'
        ]);


        $this->mock->append(new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"message\":\"File uploaded\"}"));

        $process->upload(fopen('data://text/plain,pngdata', 'r'), "test.png");

        $lastRequest = $this->mock->getLastRequest();

        $this->assertEquals('PUT', $lastRequest->getMethod());
        $this->assertEquals('/upload/test.png', $lastRequest->getUri()->getPath());
        $this->assertEquals(7, $lastRequest->getBody()->getSize());

    }


    /**
     * Test if process with uploading an input file  works
     */
    public function testIfProcessWithUploadWorks()
    {
        $process = $this->api->createProcess([
            'inputformat' => 'png',
            'outputformat' => 'pdf',
        ]);

        $this->mock->append(new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"message\":\"File uploaded\"}"));
        $this->mock->append(new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"step\":\"finished\", \"output\": {\"ext\":\"pdf\"}}"));

        $process->start([
            'input' => 'upload',
            'outputformat' => 'pdf',
            'wait' => true,
            'file' => fopen('data://text/plain,pngdata', 'r'),
        ]);


        $this->assertEquals('finished', $process->step);
        $this->assertEquals('pdf', $process->output->ext);

    }


    /**
     * Test if download of output file works
     */
    public function testIfDownloadOfOutputFileWorks()
    {
        $process = $this->api->createProcess([
            'inputformat' => 'png',
            'outputformat' => 'pdf',
        ]);

        $this->mock->append(new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"step\":\"finished\", \"output\": {\"url\":\"//outputurl\"}}"));
        $this->mock->append(new Response(200, ['Content-Type' => 'text/plain'], "outputfile"));


        $process->start([
            'input' => 'upload',
            'outputformat' => 'pdf'
        ])
            ->wait()
            ->download(__DIR__ . '/output.tmp');

        $this->assertFileExists(__DIR__ . '/output.tmp');
        $this->assertEquals("outputfile", file_get_contents(__DIR__ . '/output.tmp'));

        @unlink(__DIR__ . '/output.tmp');
    }


    /**
     * Test if download of multiple output file works
     */
    public function testIfDownloadOfMultipleOutputFileWorks()
    {
        $process = $this->api->createProcess([
            'inputformat' => 'pdf',
            'outputformat' => 'jpg',
        ]);

        $this->mock->append(new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"step\":\"finished\", \"output\": {\"url\":\"//outputurl\", \"filename\":\"output.zip\", \"files\": [\"output1.tmp\",\"output2.tmp\"]}}"));
        $this->mock->append(new Response(200, ['Content-Type' => 'text/plain'], "outputfile"));
        $this->mock->append(new Response(200, ['Content-Type' => 'text/plain'], "outputfile"));

        $process->start([
            'input' => 'upload',
            'outputformat' => 'jpg',
            'converteroptions' => [
                'page_range' => '1-2',
            ]
        ])
            ->wait()
            ->downloadAll(__DIR__);

        $this->assertFileExists(__DIR__ . '/output1.tmp');
        $this->assertFileExists(__DIR__ . '/output2.tmp');

        @unlink(__DIR__ . '/output1.tmp');
        @unlink(__DIR__ . '/output2.tmp');

    }


    /**
     * Test if the convert shortcut works
     */
    public function testIfConvertShortcutWorks()
    {

        $this->mock->append(new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], "{\"step\":\"finished\", \"output\": {\"ext\":\"pdf\"}}"));
        $process = $this->api->convert([
            'input' => 'upload',
            'inputformat' => 'pdf',
            'outputformat' => 'jpg',
        ])->wait();
        $this->assertEquals($process->step, 'finished');

    }




}
