<?php


namespace CloudConvert\Models;


class WebhookEvent
{

    public const EVENT_JOB_FINISHED = 'job.finished';
    public const EVENT_JOB_FAILED = 'job.failed';
    public const EVENT_JOB_CREATED = 'job.created';


    /**
     * @var string
     */
    protected $event;

    /**
     * @var Job|null
     */
    protected $job;

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return Job|null
     */
    public function getJob(): ?Job
    {
        return $this->job;
    }


}
