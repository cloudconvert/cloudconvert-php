<?php
namespace CloudConvert\tests;

use CloudConvert\Api;

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
        $this->api_key = getenv('API_KEY');
        $this->api = new Api($this->api_key);
    }

    /**
     * Test if process with uploading an input file (input.png) works
     */
    public function testIfProcessWithUploadWorks()
    {
        $process = $this->api->createProcess([
            'inputformat' => 'png',
            'outputformat' => 'pdf',
        ]);
        $process->start([
            'input' => 'upload',
            'outputformat' => 'pdf',
            'wait' => true,
            'file' => fopen(__DIR__ . '/input.png', 'r'),
        ]);
        $this->assertEquals($process->step, 'finished');
        $this->assertEquals($process->output->ext, 'pdf');
        $this->process = $process;
        // cleanup
        $process->delete();
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
        $process->start([
            'input' => 'upload',
            'outputformat' => 'pdf',
            'wait' => true,
            'file' => fopen(__DIR__ . '/input.png', 'r'),
        ])->download(__DIR__ . '/output.pdf');
        $this->assertFileExists(__DIR__ . '/output.pdf');
        // cleanup
        $process->delete();
        @unlink(__DIR__ . '/output.pdf');
    }


    /**
     * Test if process with uploading an input file (input.png) and custom options (quality) works
     */
    public function testIfProcessWithUploadAndCustomOptionsWorks()
    {
        $process = $this->api->createProcess([
            'inputformat' => 'png',
            'outputformat' => 'pdf',
        ]);
        $process->start([
            'input' => 'upload',
            'outputformat' => 'pdf',
            'wait' => true,
            'converteroptions' => [
                'quality' => 10,
            ],
            'file' => fopen(__DIR__ . '/input.png', 'r'),
        ]);
        $this->assertEquals($process->step, 'finished');
        $this->assertEquals($process->output->ext, 'pdf');
        $this->assertEquals($process->converter->options->quality, 10);
        // cleanup
        $process->delete();
    }

    /**
     * Test if process with downloading an input file from an URL works
     */
    public function testIfProcessWithInputDownloadWorks()
    {
        $process = $this->api->createProcess([
            'inputformat' => 'png',
            'outputformat' => 'jpg',
        ]);
        $process->start([
            'input' => 'download',
            'outputformat' => 'jpg',
            'wait' => true,
            'file' => 'https://cloudconvert.com/blog/wp-content/themes/cloudconvert/img/logo_96x60.png',
        ]);
        $this->assertEquals($process->step, 'finished');
        $this->assertEquals($process->output->ext, 'jpg');
        // cleanup
        $process->delete();
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
        $process->start([
            'input' => 'upload',
            'outputformat' => 'jpg',
            'wait' => true,
            'converteroptions' => [
                'page_range' => '1-2',
            ],
            'file' => fopen(__DIR__ . '/input.pdf', 'r'),
        ])->downloadAll(__DIR__);
        $this->assertFileExists(__DIR__ . '/input-1.jpg');
        $this->assertFileExists(__DIR__ . '/input-2.jpg');
        // cleanup
        $process->delete();
        @unlink(__DIR__ . '/input-1.jpg');
        @unlink(__DIR__ . '/input-2.jpg');
    }


    /**
     * Test if the convert shortcut works
     */
    public function testIfConvertShortcutWorks()
    {
        $process = $this->api->convert([
            'input' => 'upload',
            'inputformat' => 'pdf',
            'outputformat' => 'jpg',
            'wait' => true,
            'converteroptions' => [
                'page_range' => '1-2',
            ],
            'file' => fopen(__DIR__ . '/input.pdf', 'r'),
        ]);
        $this->assertEquals($process->step, 'finished');
        // cleanup
        $process->delete();

    }



    /**
     * Test if multiple convert shortcut works
     */
    public function testIfMultipleConvertShortcutWorks()
    {
        foreach(["input.png","input.png","input.png"] as $file) {
            $process = $this->api->convert([
                'inputformat' => 'png',
                'outputformat' => 'pdf',
                'input' => 'upload',
                'wait' => true,
                'file' => fopen(__DIR__ . '/' . $file, 'r'),
            ]);
            $this->assertEquals($process->step, 'finished');
            $this->assertEquals($process->output->ext, 'pdf');
            $this->process = $process;
            // cleanup
            $process->delete();
        }
    }



}
