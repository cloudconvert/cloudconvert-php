<?php


namespace CloudConvert\Models;


class JobCollection extends Collection
{


    /**
     * Filter job collection by status
     *
     * @param $status
     *
     * @return JobCollection
     */
    public function whereStatus($status): JobCollection
    {

        return $this->filter(function (Job $job) use ($status) {
            return $job->getStatus() === $status;
        });

    }

    /**
     * Filter job collection by status
     *
     * @deprecated Use whereStatus() instead.
     *
     * @param $status
     *
     * @return JobCollection
     */
    public function status($status): JobCollection
    {
        return $this->whereStatus($status);
    }

}
