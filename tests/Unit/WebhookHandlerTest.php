<?php


namespace CloudConvert\Tests\Unit;


use CloudConvert\Exceptions\SignatureVerificationException;
use CloudConvert\Models\ExportUrlTask;
use CloudConvert\Models\Job;
use CloudConvert\Models\Task;
use CloudConvert\Models\WebhookEvent;
use GuzzleHttp\Psr7\Request;

class WebhookHandlerTest extends TestCase
{

    public function testInvalidSignature()
    {

        $request = new Request('POST', '/webhook', [
            'CloudConvert-Signature' => 'invalid'
        ], file_get_contents(__DIR__ . '/requests/webhook_job_finished_payload.json'));

        $this->expectException(SignatureVerificationException::class);

        $this->cloudConvert->webhookHandler()->constructEventFromRequest($request, 'secret');

    }


    public function testConstructsWebhookEvent()
    {

        $request = new Request('POST', '/webhook', [
            'CloudConvert-Signature' => '576b653f726c85265a389532988f483b5c7d7d5f40cede5f5ddf9c3f02934f35'
        ], file_get_contents(__DIR__ . '/requests/webhook_job_finished_payload.json'));


        $webhookEvent = $this->cloudConvert->webhookHandler()->constructEventFromRequest($request, 'secret');


        $this->assertInstanceOf(WebhookEvent::class, $webhookEvent);
        $this->assertEquals(WebhookEvent::EVENT_JOB_FINISHED, $webhookEvent->getEvent());
        $this->assertInstanceOf(Job::class, $webhookEvent->getJob());
        $this->assertCount(3, $webhookEvent->getJob()->getTasks());
        $this->assertInstanceOf(Task::class, $webhookEvent
            ->getJob()
            ->getTasks()
            ->status(Task::STATUS_FINISHED)
            ->name('export-it')[0]);


    }

}
