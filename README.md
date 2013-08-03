cloudconvert-sample-php
=======================

This example uses the API of CloudConvert and converts a PNG file to a PDF. There are more than 100 different conversion types possible, see the resource links for details. 

Feel free to use or modify this example files! If you have questions contact us or open an issue on GitHub.

There are two different methods in this example, which are independent from each other:

Server Side Example (sample.serverside.php)
-------------------

The input file (input.png) is uploaded with CURL to CloudConvert and afterwards the output file is downloaded in the same directory (output.pdf)


Client Side Example (sample.clientside.php)
-------------------

The use can select the input file (PNG) from his computer and uploaad it directly to CloudConvert. Afterwards the status of the conversion is checked via AJAX.


Resources
---------

* [API Documentation](https://cloudconvert.org/page/api)
* [Conversion Types](https://cloudconvert.org/formats)
* [CloudConvert Blog](https://cloudconvert.org/blog)
