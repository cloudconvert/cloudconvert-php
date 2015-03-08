cloudconvert-php
=======================

This is a lightweight wrapper for the [CloudConvert](https://cloudconvert.com) API.

Feel free to use, improve or modify this wrapper! If you have questions contact us or open an issue on GitHub.




Quickstart
-------------------
```php
<?php
require __DIR__ . '/vendor/autoload.php';
use \CloudConvert\Api;
$api = new Api("your_api_key");

$api->convert([
        'inputformat' => 'png',
        'outputformat' => 'pdf',
        'input' => 'upload',
        'file' => fopen('./tests/input.png', 'r'),
    ])
    ->wait()
    ->download('./tests/output.pdf');
?>
```

You can use the [CloudConvert API Console](https://cloudconvert.com/apiconsole) to generate ready-to-use PHP code snippets using this wrapper.


Install with Composer
-------------------
To download this wrapper and integrate it inside your PHP application, you can use [Composer](https://getcomposer.org).

Add the repository in your **composer.json** file or, if you don't already have this file, create it at the root of your project with this content:

```json
{
    "name": "Example Application",
    "description": "This is an example",
    "require": {
        "cloudconvert/cloudconvert-php": "dev-master"
    }
}

```

Then, you can install CloudConvert APIs wrapper and dependencies with:

    php composer.phar install

This will install ``cloudconvert/cloudconvert-php`` to ``./vendor``, along with other dependencies including ``autoload.php``.

Install with Phar
-------------------
If you don't want to use composer, you can download the .phar release from the "Releases" tab on GitHub.

```php
<?php
require 'phar://cloudconvert-php.phar/vendor/autoload.php';
use \CloudConvert\Api;
$api = new Api("your_api_key");

//...
```

Using with Callback
-------------------

This is a non-blocking example for server side conversions: The public URL of the input file and a callback URL is sent to CloudConvert. CloudConvert will trigger this callback URL if the conversion is finished.

```php
<?php
require __DIR__ . '/vendor/autoload.php';
use \CloudConvert\Api;
$api = new Api("your_api_key");

$process = $api->createProcess([
    'inputformat' => 'png',
    'outputformat' => 'jpg',
]);

$process->start([
    'outputformat' => 'jpg',
    'converteroptions' => [
        'quality' => 75,
    ],
    'input' => 'download',
    'file' => 'https://cloudconvert.com/blog/wp-content/themes/cloudconvert/img/logo_96x60.png',
    'callback' => 'http://_INSERT_PUBLIC_URL_TO_/callback.php'
]);

echo "Conversion was started in background :-)";
?>
```

Using the following **callback.php** you can retrieve the finished process and download the output file.

```php
<?php
require __DIR__ . '/vendor/autoload.php';
use \CloudConvert\Api;
use \CloudConvert\Process;
$api = new Api("your_api_key");

$process = new Process($api, $_REQUEST['url']);
$process->refresh()->download("output.jpg");

?>
```


Download of multiple output files
-------------------

In some cases it might be possible that there are multiple output files (e.g. converting a multi-page PDF to JPG). You can download them all to one directory using the ``downloadAll()`` method.

```php
<?php
require __DIR__ . '/vendor/autoload.php';
use \CloudConvert\Api;
$api = new Api("your_api_key");

$process = $api->convert([
        'inputformat' => 'pdf',
        'outputformat' => 'jpg',
        'converteroptions' => [
            'page_range' => '1-3',
        ],
        'input' => 'download',
        'file' => fopen('./tests/input.pdf', 'r'),
    ])
    ->wait()
    ->downloadAll('./tests/');
?>
```

Alternatively you can iterate over ``$process->output->files`` and download them seperately using ``$process->download($localfile, $remotefile)``.


Catching Exceptions
-------------------
The following example shows how to catch the different exception types which can occur at conversions:

```php
<?php
require __DIR__ . '/vendor/autoload.php';
use \CloudConvert\Api;

$api = new Api("your_api_key");

try {

    $api->convert([
        'inputformat' => 'pdf',
        'outputformat' => 'jpg',
        'input' => 'upload',
        'file' => fopen('./tests/input.pdf', 'r'),
    ])
        ->wait()
        ->downloadAll('./tests/');

} catch (\CloudConvert\Exceptions\ApiBadRequestException $e) {
    echo "Something with your request is wrong: " . $e->getMessage();
} catch (\CloudConvert\Exceptions\ApiConversionFailedException $e) {
    echo "Conversion failed, maybe because of a broken input file: " . $e->getMessage();
}  catch (\CloudConvert\Exceptions\ApiTemporaryUnavailableException $e) {
    echo "API temporary unavailable: " . $e->getMessage() ."\n";
    echo "We should retry the conversion in " . $e->retryAfter . " seconds";
} catch (Exception $e) {
    // network problems, etc..
    echo "Something else went wrong: " . $e->getMessage() . "\n";
}
```



How to build the documentation?
-------------------------------

Documentation is based on phpdocumentor. To install it with other quality tools,
you can install local npm project in a clone a project

    git clone https://github.com/LunawebLtd/cloudconvert-php.git
    cd cloudconvert-php
    php composer.phar install
    npm install

To generate documentation, it's possible to use directly:

    grunt phpdocs

Documentation is available in docs/ directory.

How to run tests?
-----------------

Tests are based on phpunit. To install it with other quality tools, you can install
local npm project in a clone a project

    git https://github.com/LunawebLtd/cloudconvert-php.git
    cd cloudconvert-php
    php composer.phar install
    npm install

Edit **phpunit.xml** file with your API Key to pass functionals tests. Then,
you can run directly unit and functionals tests with grunt.

    grunt


Resources
---------

* [API Documentation](https://cloudconvert.com/apidoc)
* [Conversion Types](https://cloudconvert.com/formats)
* [CloudConvert Blog](https://cloudconvert.com/blog)
