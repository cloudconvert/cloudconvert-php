<?php


namespace CloudConvert\Models;


class Task
{

    public const STATUS_WATING = 'waiting';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_ERROR = 'error';
    public const STATUS_FINISHED = 'finished';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $created_at;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $started_at;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $ended_at;

    /**
     * @var string|null
     */
    protected $status;

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $code;

    /**
     * @var object|null
     */
    protected $payload;

    /**
     * @var object|null
     */
    protected $result;

    /**
     * @var TaskCollection[Task]|null
     */
    protected $depends_on_tasks;

    /**
     * @var string|null
     */
    protected $job_id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @var object|null
     */
    protected $links;

    /**
     * Task constructor.
     *
     * @param string|null $operation
     * @param string|null $name
     */
    public function __construct(string $operation = null, string $name = null)
    {
        $this->operation = $operation;
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }


    /**
     * @return \DateTimeImmutable|null
     */
    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->started_at;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->ended_at;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return object|null
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return object|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return TaskCollection[Task]|null
     */
    public function getDependsOnTasks(): ?TaskCollection
    {
        return $this->depends_on_tasks;
    }

    /**
     * @return string
     */
    public function getJobId(): ?string
    {
        return $this->job_id;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @return object
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Set a task payload parameter
     *
     * @param string $parameter
     * @param        $value
     *
     * @return $this
     */
    public function set(string $parameter, $value)
    {
        if (!$this->payload) {
            $this->payload = [];
        }
        $this->payload[$parameter] = $value;
        return $this;
    }


}
