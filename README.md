cloudconvert-sample-php
=======================

This example uses the API of CloudConvert and converts a PNG file to a PDF. There are more than 150 different conversion types possible (see the resource links below for details). 

Feel free to use or modify this example files! If you have questions contact us or open an issue on GitHub.

There are three different methods in this example, which are independent from each other:

Server Side Uploading
-------------------
The input file (input.png) is uploaded with CURL to CloudConvert and afterwards the output file is downloaded in the same directory (output.pdf)

```php
$process = CloudConvert::createProcess("png", "pdf", $apikey);

$process-> upload("input.png", "pdf" );

if ($process-> waitForConversion()) {
   $process -> download("output.pdf");
    echo "Conversion done :-)";
} else {
    echo "Something went wrong :-(";
}
```

The full example can be found here: [sample.serverside.php](sample.serverside.php)


Server Side Uploading with Callback
-------------------

This is a non-blocking example for server side conversions: The public URL of the input file and a callback URL is sent to CloudConvert. CloudConvert will trigger this callback URL if the conversion is finished.

```php
$process = CloudConvert::createProcess("png", "pdf", $apikey);

$process -> setOption("callback", "http://trigger.me.after.conversion.is/done.php");
$process -> uploadByUrl("http://public.url.to/input.png", "input.png", "pdf");

echo "Conversion was started in background :-)";
```

The full example can be found here: [sample.serverside.callback.php](sample.serverside.callback.php)

Client Side Uploading
-------------------

The user can select the input file (PNG) from his computer and upload it directly to CloudConvert. Afterwards the status of the conversion is checked via AJAX.

The example can be found here: [sample.clientside.php](sample.clientside.php)



Resources
---------

* [API Documentation](https://cloudconvert.org/page/api)
* [Conversion Types](https://cloudconvert.org/formats)
* [CloudConvert Blog](https://cloudconvert.org/blog)
