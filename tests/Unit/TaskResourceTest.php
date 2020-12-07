<?php


namespace CloudConvert\Tests\Unit;


use CloudConvert\Models\ExportUrlTask;
use CloudConvert\Models\ImportUploadTask;
use CloudConvert\Models\ImportUrlTask;
use CloudConvert\Models\Task;


use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\CallbackRequestMatcher;
use Http\Message\RequestMatcher\RequestMatcher;
use Psr\Http\Message\RequestInterface;

class TaskResourceTest extends TestCase
{

    public function testGet()
    {


        $response = new Response(200, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/task.json'));


        $this->getMockClient()->on(new RequestMatcher('/v2/tasks/4c80f1ae-5b3a-43d5-bb58-1a5c4eb4e46b', null, 'GET'),
            $response);


        $task = $this->cloudConvert->tasks()->get('4c80f1ae-5b3a-43d5-bb58-1a5c4eb4e46b');

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('4c80f1ae-5b3a-43d5-bb58-1a5c4eb4e46b', $task->getId());
        $this->assertEquals('export-1', $task->getName());
        $this->assertEquals(Task::STATUS_ERROR, $task->getStatus());
        $this->assertEquals('INPUT_TASK_FAILED', $task->getCode());
        $this->assertEquals('Input task has failed', $task->getMessage());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getEndedAt());
        $this->assertNull($task->getStartedAt());
        $this->assertCount(1, $task->getDependsOnTasks());
        $this->assertInstanceOf(Task::class, $task->getDependsOnTasks()[0]);
        $this->assertEquals('6df0920a-7042-4e87-be52-f38a0a29a67e', $task->getDependsOnTasks()[0]->getId());


    }


    public function testAll()
    {


        $response = new Response(200, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/tasks.json'));


        $this->getMockClient()->on(new RequestMatcher('/v2/tasks', null, 'GET'),
            $response);


        $tasks = $this->cloudConvert->tasks()->all();

        $this->assertCount(1, $tasks);
        $this->assertInstanceOf(Task::class, $tasks[0]);
        $this->assertEquals('4c80f1ae-5b3a-43d5-bb58-1a5c4eb4e46b', $tasks[0]->getId());

    }


    public function testCreateImportUrlTask()
    {

        $task = (new Task('import/url', 'test'))
            ->set('url', 'http://invalid.url')
            ->set('filename', 'test.file');


        $response = new Response(201, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/task_created.json'));


        $this->getMockClient()->on(
            new CallbackRequestMatcher(function (RequestInterface $request) {
                if (strpos($request->getUri(), '/import/url') === false) {
                    return false;
                }
                $body = json_decode($request->getBody(), true);
                if ($body['name'] !== 'test'
                    || $body['url'] !== 'http://invalid.url'
                    || $body['filename'] !== 'test.file') {
                    return false;
                }
                return true;

            }), $response);


        $this->cloudConvert->tasks()->create($task);

        $this->assertNotNull($task->getId());
        $this->assertNotNull($task->getCreatedAt());
        $this->assertEquals([
            'name'     => 'test',
            'url'      => 'http://invalid.url',
            'filename' => 'test.file'
        ], (array)$task->getPayload());
        $this->assertEquals(Task::STATUS_WATING, $task->getStatus());


    }


    public function testRefresh()
    {

        $task = new Task('import/url', 'test');

        $reflection = new \ReflectionClass($task);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($task, '4c80f1ae-5b3a-43d5-bb58-1a5c4eb4e46b');


        $this->assertNull($task->getCreatedAt());

        $response = new Response(200, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/task.json'));


        $this->getMockClient()->on(new RequestMatcher('/v2/tasks/4c80f1ae-5b3a-43d5-bb58-1a5c4eb4e46b', null, 'GET'),
            $response);


        $this->cloudConvert->tasks()->refresh($task);


        $this->assertNotNull($task->getCreatedAt());
        $this->assertEquals(Task::STATUS_ERROR, $task->getStatus());
        $this->assertEquals('Input task has failed', $task->getMessage());

    }


    public function testUploadFile()
    {

        $task = new Task('import/upload', 'upload-test');

        $response = new Response(200, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/upload_task_created.json'));


        $this->getMockClient()->on(new RequestMatcher('/v2/import/upload', null, 'POST'),
            $response);


        $this->cloudConvert->tasks()->create($task);


        $this->getMockClient()->on(
            new CallbackRequestMatcher(function (RequestInterface $request) use ($task) {
                if ((string)$request->getUri() !== $task->getResult()->form->url) {
                    return false;
                }

                $body = (string)$request->getBody();

                foreach ((array)$task->getResult()->form->parameters as $parameter => $value) {
                    $this->assertStringContainsString('name="' . $parameter . '"', $body);
                    $this->assertStringContainsString((string)$value, $body);
                }

                return true;

            }), new Response(201, [], ""));


        $response = $this->cloudConvert->tasks()->upload($task, "filecontent");

        $this->assertEquals(201, $response->getStatusCode());


    }


}
