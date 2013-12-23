<?php
/*
 * Converts the file input.png server side to output.pdf
 *
 */
 
// for testing it could be helpful...
ini_set('display_errors', 1);

require_once 'CloudConvert.class.php';

// insert your API key here
$apikey="";

$process = CloudConvert::createProcess("png", "pdf", $apikey);

// set some options here...
// $process -> setOption("email", "1");

$process-> upload("input.png", "pdf" );

if ($process-> waitForConversion()) {
   $process -> download("output.pdf");
    echo "Conversion done :-)";

    ?>
    <br>
    <a href="input.png">input.png</a></br>
    <a href="output.pdf">output.pdf</a></br>
    <?
} else {
    echo "Something went wrong :-(";
}

// maybe delete process from CloudConvert
// $process -> delete();

?>