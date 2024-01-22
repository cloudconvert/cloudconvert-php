cloudconvert-php
=======================

This is the official PHP SDK for the [CloudConvert](https://cloudconvert.com/api/v2) **API v2**.

[![Tests](https://github.com/cloudconvert/cloudconvert-php/actions/workflows/run-tests.yml/badge.svg)](https://github.com/cloudconvert/cloudconvert-php/actions/workflows/run-tests.yml)
[![Latest Stable Version](https://poser.pugx.org/cloudconvert/cloudconvert-php/v/stable)](https://packagist.org/packages/cloudconvert/cloudconvert-php)
[![Total Downloads](https://poser.pugx.org/cloudconvert/cloudconvert-php/downloads)](https://packagist.org/packages/cloudconvert/cloudconvert-php)


Install
-------------------

To install the PHP SDK you will need to be using [Composer]([https://getcomposer.org/)
in your project. 
 
Install the SDK alongside Guzzle:

```bash
composer require cloudconvert/cloudconvert-php guzzlehttp/guzzle
```

This package is not tied to any specific HTTP client by using [PSR-7](https://www.php-fig.org/psr/psr-7/), [PSR-17](https://www.php-fig.org/psr/psr-17/), [PSR-18](https://www.php-fig.org/psr/psr-18/), and [HTTPlug](https://httplug.io/). Therefore, you will also need to install packages that provide [`psr/http-client-implementation`](https://packagist.org/providers/psr/http-client-implementation) and [`psr/http-factory-implementation`](https://packagist.org/providers/psr/http-factory-implementation) (for example Guzzle).



Creating Jobs
-------------------
```php
use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;


$cloudconvert = new CloudConvert([
    'api_key' => 'API_KEY',
    'sandbox' => false
]);


$job = (new Job())
    ->setTag('myjob-1')
    ->addTask(
        (new Task('import/url', 'import-my-file'))
            ->set('url','https://my-url')
    )
    ->addTask(
        (new Task('convert', 'convert-my-file'))
            ->set('input', 'import-my-file')
            ->set('output_format', 'pdf')
            ->set('some_other_option', 'value')
    )
    ->addTask(
        (new Task('export/url', 'export-my-file'))
            ->set('input', 'convert-my-file')
    );

$cloudconvert->jobs()->create($job)

```

You can use the [CloudConvert Job Builder](https://cloudconvert.com/api/v2/jobs/builder) to see the available options for the various task types.


Uploading Files
-------------------
Uploads to CloudConvert are done via `import/upload` tasks (see the [docs](https://cloudconvert.com/api/v2/import#import-upload-tasks)). This SDK offers a convenient upload method:

```php
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;


$job = (new Job())
    ->addTask(new Task('import/upload','upload-my-file'))
    ->addTask(
        (new Task('convert', 'convert-my-file'))
            ->set('input', 'upload-my-file')
            ->set('output_format', 'pdf')
    )
    ->addTask(
        (new Task('export/url', 'export-my-file'))
            ->set('input', 'convert-my-file')
    );

$job = $cloudconvert->jobs()->create($job);

$uploadTask = $job->getTasks()->whereName('upload-my-file')[0];

$cloudconvert->tasks()->upload($uploadTask, fopen('./file.pdf', 'r'), 'file.pdf');
```
The `upload()` method accepts a string, PHP resource or PSR-7 `StreamInterface` as second parameter.

You can also directly allow clients to upload files to CloudConvert:

```html
<form action="<?=$uploadTask->getResult()->form->url?>"
      method="POST"
      enctype="multipart/form-data">
    <? foreach ((array)$uploadTask->getResult()->form->parameters as $parameter => $value) { ?>
        <input type="hidden" name="<?=$parameter?>" value="<?=$value?>">
    <? } ?>
    <input type="file" name="file">
    <input type="submit">
</form>
```


Downloading Files
-------------------

CloudConvert can generate public URLs for using `export/url` tasks. You can use the PHP SDK to download the output files when the Job is finished.

```php
$cloudconvert->jobs()->wait($job); // Wait for job completion

foreach ($job->getExportUrls() as $file) {

    $source = $cloudconvert->getHttpTransport()->download($file->url)->detach();
    $dest = fopen('output/' . $file->filename, 'w');
    
    stream_copy_to_stream($source, $dest);

}
```

The `download()` method returns a PSR-7 `StreamInterface`, which can be used as a PHP resource using `detach()`.



Webhooks
-------------------

Webhooks can be created on the [CloudConvert Dashboard](https://cloudconvert.com/dashboard/api/v2/webhooks) and you can also find the required signing secret there.

```php
$cloudconvert = new CloudConvert([
    'api_key' => 'API_KEY',
    'sandbox' => false
]);

$signingSecret = '...'; // You can find it in your webhook settings

$payload = @file_get_contents('php://input');
$signature = $_SERVER['HTTP_CLOUDCONVERT_SIGNATURE'];

try {
    $webhookEvent = $cloudconvert->webhookHandler()->constructEvent($payload, $signature, $signingSecret);
} catch(\CloudConvert\Exceptions\UnexpectedDataException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch(\CloudConvert\Exceptions\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}

$job = $webhookEvent->getJob();

$job->getTag(); // can be used to store an ID

$exportTask = $job->getTasks()
            ->whereStatus(Task::STATUS_FINISHED) // get the task with 'finished' status ...
            ->whereName('export-it')[0];        // ... and with the name 'export-it'
// ...

```

Alternatively, you can construct a `WebhookEvent` using a PSR-7 `RequestInterface`:
```php
$webhookEvent = $cloudconvert->webhookHandler()->constructEventFromRequest($request, $signingSecret);
```

Signed URLs
-------------------

Signed URLs allow converting files on demand only using URL query parameters. The PHP SDK allows to generate such URLs. Therefore, you need to obtain a signed URL base and a signing secret on the [CloudConvert Dashboard](https://cloudconvert.com/dashboard/api/v2/signed-urls).

```php
$cloudconvert = new CloudConvert([
    'api_key' => 'API_KEY',
    'sandbox' => false
]);

$job = (new Job())
    ->addTask(
        (new Task('import/url', 'import-my-file'))
            ->set('url', 'https://my.url/file.docx')
    )
    ->addTask(
        (new Task('convert', 'convert-my-file'))
            ->set('input', 'import-my-file')
            ->set('output_format', 'pdf')
    )
    ->addTask(
        (new Task('export/url', 'export-my-file'))
            ->set('input', 'convert-my-file')
    );

$signedUrlBase = 'SIGNED_URL_BASE';
$signingSecret = 'SIGNED_URL_SIGNING_SECRET';
$url = $cloudConvert->signedUrlBuilder()->createFromJob($signedUrlBase, $signingSecret, $job, 'CACHE_KEY');
```



Setting a Region
-----------------

By default, the region in your [account settings](https://cloudconvert.com/dashboard/region) is used. Alternatively, you can set a fixed region:

```php
// Pass the region to the constructor
$cloudconvert = new CloudConvert([
    'api_key' => 'API_KEY',
    'sandbox' => false,
    'region'  => 'us-east'
]);
```


Unit Tests
-----------------

    vendor/bin/phpunit --testsuite unit


Integration Tests
-----------------

    vendor/bin/phpunit --testsuite integration

By default, this runs the integration tests against the Sandbox API with an official CloudConvert account. If you would like to use your own account, you can set your API key using the `CLOUDCONVERT_API_KEY` enviroment variable. In this case you need to whitelist the following MD5 hashes for Sandbox API (using the CloudConvert dashboard).

    53d6fe6b688c31c565907c81de625046  input.pdf
    99d4c165f77af02015aa647770286cf9  input.png

Resources
---------

* [API Documentation](https://cloudconvert.com/api/v2)
* [CloudConvert Blog](https://cloudconvert.com/blog)
