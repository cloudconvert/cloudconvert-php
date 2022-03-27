<?php

namespace CloudConvert\Tests\Unit;

use CloudConvert\Models\Job;
use CloudConvert\Models\Task;

class SignedUrlBuilderTest extends TestCase
{

    public function testCreateSignedUrl() {


        $job = (new Job())
            ->addTask(
                (new Task('import/url', 'import-it'))
                    ->set('url', 'https://some.url')
                    ->set('filename', 'test.png')
            )
            ->addTask(
                (new Task('export/url', 'export-it'))
                    ->set('input', 'import-it')
                    ->set('inline', true)
            );


        $url =  $this->cloudConvert->signedUrlBuilder()->createFromJob('https://s.cloudconvert.com/b3d85428-584e-4639-bc11-76b7dee9c109', 'NT8dpJkttEyfSk3qlRgUJtvTkx64vhyX', $job, 'mykey');

        $this->assertStringStartsWith('https://s.cloudconvert.com/', $url);
        $this->assertStringContainsString('?job=', $url);
        $this->assertStringContainsString('&cache_key=mykey', $url);
        $this->assertStringContainsString('&s=fb2760b572f652316d2ca218cec980024057ff108a472425c9fcc6136709cbe8', $url);


    }

}