<?php


namespace CloudConvert\Tests\Integration;


use CloudConvert\Models\ImportUploadTask;
use CloudConvert\Models\ImportUrlTask;
use CloudConvert\Models\Task;

class TaskTest extends TestCase
{

    public function testCreateImportUrlTask()
    {

        $task = (new Task('import/url', 'url-test'))
            ->set('url', 'http://invalid.url')
            ->set('filename', 'test.file');

        $this->cloudConvert->tasks()->create($task);

        $this->assertNotNull($task->getId());
        $this->assertNotNull($task->getCreatedAt());
        $this->assertEquals('import/url', $task->getOperation());
        $this->assertEquals([
            'name'     => 'url-test',
            'url'      => 'http://invalid.url',
            'filename' => 'test.file'
        ], (array)$task->getPayload());
        $this->assertEquals(Task::STATUS_WATING, $task->getStatus());


    }


    public function testUploadFile()
    {

        $task = (new Task('import/upload', 'upload-test'));

        $this->cloudConvert->tasks()->create($task);

        $response = $this->cloudConvert->tasks()->upload($task, fopen(__DIR__ . '/files/input.pdf', 'r'));

        $this->assertEquals(201, $response->getStatusCode());

        while ($task->getStatus() !== Task::STATUS_FINISHED) {
            sleep(1);
            $this->cloudConvert->tasks()->refresh($task);
        }

        $this->assertEquals('input.pdf', $task->getResult()->files[0]->filename);


    }


}
