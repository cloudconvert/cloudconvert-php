<?php


namespace CloudConvert\Models;


class Job
{

    public const STATUS_WATING = 'waiting';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_ERROR = 'error';
    public const STATUS_FINISHED = 'finished';
    public const ATTR_TAG_KEY = 'tag';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var \DateTimeImmutable
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
     * @var TaskCollection[Task]|null
     */
    protected $tasks;

    /**
     * @var object|null
     */
    protected $links;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTag(): ?string
    {
        return null !== ($tag = $this->attributes[self::ATTR_TAG_KEY]) ? $tag : null;
    }

    /**
     * @return array
     */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     * @param string|null $value
     *
     * @return Job
     */
    public function setAttribute(string $key, ?string $value): Job
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @param string|null $tag
     *
     * @return Job
     */
    public function setTag(?string $tag): Job
    {
        $this->attributes[self::ATTR_TAG_KEY] = $tag;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
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
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return TaskCollection[Task]|null
     */
    public function getTasks(): ?TaskCollection
    {
        return $this->tasks;
    }


    /**
     * @return object|null
     */
    public function getLinks()
    {
        return $this->links;
    }


    /**
     * @param Task $task
     *
     * @return Job
     */
    public function addTask(Task $task): Job
    {
        if (!$this->tasks) {
            $this->tasks = new TaskCollection();
        }
        $this->tasks[] = $task;
        return $this;
    }


    /*
     * return array
     */
    public function getExportUrls()
    {
        $files = [];
        foreach ($this->getTasks()
                     ->status(Task::STATUS_FINISHED)
                     ->operation('export/url') as $exportTask) {
            $files = array_merge($files, $exportTask->getResult()->files ?? []);
        }
        return $files;
    }


}
