<?php
/*
 * Converts the file input.png server side to output.pdf
 *
 */

require_once 'CloudConvert.class.php';

// insert your API key here
$apikey="";

$converter = new CloudConvert("png", "pdf", $apikey);

if ($converter -> convert("input.png", "pdf", "output.pdf")) {
    echo "Conversion done :-)";
    ?>
    <br>
    <a href="input.png">input.png</a></br>
    <a href="output.pdf">output.pdf</a></br>
    <?
} else {
    echo "Something went wrong :-(";
}
?>