<?php


namespace CloudConvert\Resources;


use CloudConvert\Models\Job;
use CloudConvert\Models\JobCollection;

class JobsResource extends AbstractResource
{

    /**
     * @param string     $id
     *
     * @param array|null $query
     *
     * @return Job
     */
    public function get(string $id, $query = null): Job
    {
        $response = $this->httpTransport->get($this->httpTransport->getBaseUri() . '/jobs/' . $id, $query ?? []);
        return $this->hydrator->createObjectByResponse(Job::class, $response);


    }

    /**
     * @param array|null $query
     *
     * @return JobCollection
     */
    public function all($query = null): JobCollection
    {
        $response = $this->httpTransport->get($this->httpTransport->getBaseUri() . '/jobs', $query ?? []);
        return $this->hydrator->hydrateArrayByResponse(new JobCollection(), Job::class, $response);
    }


    /**
     * @param Job $job
     *
     * @return Job
     */
    public function create(Job $job): Job
    {
        $tasks = [];
        foreach ($job->getTasks() ?? [] as $task) {
            $tasks[$task->getName()] = array_merge(
                ['operation' => $task->getOperation()],
                $task->getPayload() ?? []
            );
        }
        $response = $this->httpTransport->post($this->httpTransport->getBaseUri() . '/jobs', [
            'tasks'       => $tasks,
            'tag'         => $job->getTag(),
            'webhook_url' => $job->getWebhookUrl()
        ]);
        return $this->hydrator->hydrateObjectByResponse($job, $response);
    }


    /**
     * @param Job        $job
     *
     * @param array|null $query
     *
     * @return Job
     */
    public function refresh(Job $job, $query = null): Job
    {
        $response = $this->httpTransport->get($this->httpTransport->getBaseUri() . '/jobs/' . $job->getId(),
            $query ?? []);
        return $this->hydrator->hydrateObjectByResponse($job, $response);
    }

    /**
     * @param Job $job
     *
     * @return Job
     */
    public function wait(Job $job): Job
    {
        $response = $this->httpTransport->get($this->httpTransport->getBaseUri() . '/jobs/' . $job->getId() . '/wait');
        return $this->hydrator->hydrateObjectByResponse($job, $response);
    }

    /**
     * @param Job $job
     */
    public function delete(Job $job): void
    {
        $this->httpTransport->delete($this->httpTransport->getBaseUri() . '/jobs/' . $job->getId());
    }


}
