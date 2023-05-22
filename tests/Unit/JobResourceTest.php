<?php


namespace CloudConvert\Tests\Unit;



use CloudConvert\Models\Job;
use CloudConvert\Models\Task;


use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\CallbackRequestMatcher;
use Http\Message\RequestMatcher\RequestMatcher;
use Psr\Http\Message\RequestInterface;

class JobResourceTest extends TestCase
{

    public function testGet()
    {


        $response = new Response(200, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/job.json'));


        $this->getMockClient()->on(new RequestMatcher('/v2/jobs/cd82535b-0614-4b23-bbba-b24ab0e892f7', null, 'GET'),
            $response);


        $job = $this->cloudConvert->jobs()->get('cd82535b-0614-4b23-bbba-b24ab0e892f7');

        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals('cd82535b-0614-4b23-bbba-b24ab0e892f7', $job->getId());
        $this->assertEquals('test-1234', $job->getTag());
        $this->assertEquals(Job::STATUS_ERROR, $job->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $job->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $job->getEndedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $job->getStartedAt());
        $this->assertCount(3, $job->getTasks());
        $this->assertInstanceOf(Task::class, $job->getTasks()[0]);
        $this->assertEquals('4c80f1ae-5b3a-43d5-bb58-1a5c4eb4e46b', $job->getTasks()[0]->getId());


    }

    public function testAll()
    {


        $response = new Response(200, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/jobs.json'));


        $this->getMockClient()->on(new RequestMatcher('/v2/jobs', null, 'GET'),
            $response);


        $jobs = $this->cloudConvert->jobs()->all();

        $this->assertCount(1, $jobs);
        $this->assertInstanceOf(Job::class, $jobs[0]);
        $this->assertEquals('bd7d06b4-60fb-472b-b3a3-9034b273df07', $jobs[0]->getId());


    }


    public function testCreateJob()
    {

        $job = (new Job())
            ->addTask(
                (new Task('import/url', 'import-it'))
                    ->set('url', 'http://invalid.url')
                    ->set('filename', 'test.file')
            )
            ->addTask(
                (new Task('convert', 'convert-it'))
                    ->set('input', 'import-it')
                    ->set('output_format', 'pdf')
            );


        $response = new Response(201, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/job_created.json'));


        $this->getMockClient()->on(
            new CallbackRequestMatcher(function (RequestInterface $request) {
                if (strpos($request->getUri(), '/jobs') === false) {
                    return false;
                }
                $body = json_decode($request->getBody(), true);
                if (!isset($body['tasks']['import-it'])
                    || !isset($body['tasks']['convert-it'])
                    || $body['tasks']['import-it']['operation'] !== 'import/url'
                    || $body['tasks']['import-it']['url'] !== 'http://invalid.url'
                ) {
                    return false;
                }
                return true;

            }), $response);


        $this->cloudConvert->jobs()->create($job);

        $this->assertNotNull($job->getId());
        $this->assertNotNull($job->getCreatedAt());
        $this->assertCount(2, $job->getTasks());

        $task1 = $job->getTasks()[0];
        $task2 = $job->getTasks()[1];

        $this->assertEquals('import/url', $task1->getOperation());
        $this->assertEquals('import-it', $task1->getName());
        $this->assertEquals([
            'operation' => 'import/url',
            'url'       => 'http://invalid.url',
            'filename'  => 'test.file'
        ], (array)$task1->getPayload());

        $this->assertEquals('convert', $task2->getOperation());
        $this->assertEquals('convert-it', $task2->getName());
        $this->assertEquals([
            'operation'     => 'convert',
            'input'         => ['import-it'],
            'output_format' => 'pdf',
        ], (array)$task2->getPayload());


    }


    public function testGetExportUrls()
    {


        $response = new Response(200, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/job_export_urls.json'));


        $this->getMockClient()->on(new RequestMatcher('/v2/jobs/cd82535b-0614-4b23-bbba-b24ab0e892f7', null, 'GET'),
            $response);


        $job = $this->cloudConvert->jobs()->get('cd82535b-0614-4b23-bbba-b24ab0e892f7');

        $this->assertInstanceOf(Job::class, $job);

        $urls = $job->getExportUrls();


        $this->assertCount(2, $urls);

        $this->assertEquals('file.mp4', $urls[0]->filename);
        $this->assertEquals('https://storage.cloudconvert.com/file.mp4', $urls[0]->url);

        $this->assertEquals('file2.mp4', $urls[1]->filename);

    }


}
