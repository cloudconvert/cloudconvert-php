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
            'url'      => 'http://invalid.url',
            'filename' => 'test.file'
        ], (array)$task->getPayload());
        $this->assertEquals(Task::STATUS_WATING, $task->getStatus());

        $this->cloudConvert->tasks()->delete($task);


    }


    public function testUploadFile()
    {

        $task = (new Task('import/upload', 'upload-test'));

        $this->cloudConvert->tasks()->create($task);

        $response = $this->cloudConvert->tasks()->upload($task, fopen(__DIR__ . '/files/input.pdf', 'r'));

        $this->assertEquals(201, $response->getStatusCode());

        $this->cloudConvert->tasks()->wait($task);
        $this->assertEquals(Task::STATUS_FINISHED, $task->getStatus());

        $this->assertEquals('input.pdf', $task->getResult()->files[0]->filename);

        $this->cloudConvert->tasks()->delete($task);


    }


}
