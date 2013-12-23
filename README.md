cloudconvert-sample-php
=======================

This example uses the API of CloudConvert and converts a PNG file to a PDF. There are more than 150 different conversion types possible, see the resource links for details. 

Feel free to use or modify this example files! If you have questions contact us or open an issue on GitHub.

There are three different methods in this example, which are independent from each other:

Server Side Example (sample.serverside.php)
-------------------

The input file (input.png) is uploaded with CURL to CloudConvert and afterwards the output file is downloaded in the same directory (output.pdf)


Client Side Example (sample.clientside.php)
-------------------

The user can select the input file (PNG) from his computer and upload it directly to CloudConvert. Afterwards the status of the conversion is checked via AJAX.


Server Side Example with Callback (sample.serverside.callback.php)
-------------------

This is a non-blocking example for server side conversions: The public URL of the input file and a callback URL is sent to CloudConvert. CloudConvert will trigger this callback URL if the conversion is finished.

Resources
---------

* [API Documentation](https://cloudconvert.org/page/api)
* [Conversion Types](https://cloudconvert.org/formats)
* [CloudConvert Blog](https://cloudconvert.org/blog)
