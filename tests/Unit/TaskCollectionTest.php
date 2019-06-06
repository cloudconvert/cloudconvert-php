<?php


namespace CloudConvert\Tests\Unit;


use CloudConvert\Models\Task;
use CloudConvert\Models\TaskCollection;

class TaskCollectionTest extends TestCase
{


    public function testFilterByStatus() {


        $task1 = new Task('import/url', 'test');
        $reflection = new \ReflectionClass($task1);
        $property = $reflection->getProperty('status');
        $property->setAccessible(true);
        $property->setValue($task1, Task::STATUS_FINISHED);

        $task2 = new Task('import/url', 'test');
        $reflection = new \ReflectionClass($task1);
        $property = $reflection->getProperty('status');
        $property->setAccessible(true);
        $property->setValue($task2, Task::STATUS_ERROR);

        $task3 = new Task('import/url', 'test');
        $reflection = new \ReflectionClass($task1);
        $property = $reflection->getProperty('status');
        $property->setAccessible(true);
        $property->setValue($task3, Task::STATUS_WATING);

        $collection = new TaskCollection([$task1, $task2, $task3]);


        $filtered = $collection->status(Task::STATUS_ERROR);

        $this->assertCount(1, $filtered);

        $this->assertCount(3, $collection); // original collection not modified

        $this->assertEquals($filtered[0], $task2);




    }


}
