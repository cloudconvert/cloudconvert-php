<?php


namespace CloudConvert\Models;


class TaskCollection extends Collection
{

    /**
     * Filter task collection by status
     *
     * @param $status
     *
     * @return TaskCollection
     */
    public function whereStatus($status): TaskCollection
    {

        return $this->filter(function (Task $task) use ($status) {
            return $task->getStatus() === $status;
        });

    }

    /**
     * Filter task collection by status
     *
     * @param $status
     *
     * @return TaskCollection
     * @deprecated Use whereStatus() instead
     *
     */
    public function status($status): TaskCollection
    {
        return $this->whereStatus($status);
    }

    /**
     * Filter task collection by task name
     *
     * @param $name
     *
     * @return TaskCollection
     */
    public function whereName($name): TaskCollection
    {

        return $this->filter(function (Task $task) use ($name) {
            return $task->getName() === $name;
        });

    }


    /**
     * Filter task collection by task name
     *
     * @param $name
     *
     * @return TaskCollection
     * @deprecated Use whereName() instead
     *
     */
    public function name($name): TaskCollection
    {
        return $this->whereName($name);
    }


    /**
     * Filter task collection by operation
     *
     * @param $operation
     *
     * @return TaskCollection
     */
    public function whereOperation($operation): TaskCollection
    {

        return $this->filter(function (Task $task) use ($operation) {
            return $task->getOperation() === $operation;
        });

    }

    /**
     * Filter task collection by operation
     *
     * @param $operation
     *
     * @return TaskCollection
     * @deprecated Use whereOperation() instead
     *
     */
    public function operation($operation): TaskCollection
    {
        return $this->whereOperation($operation);
    }

}
