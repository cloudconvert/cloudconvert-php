<?php
/*
 * Converts the file *.png server side to output.pdf
 * Uploads the file from client side and track the status
 */


// for testing it could be helpful...
ini_set('display_errors', 1);
 
 
require_once 'CloudConvert.class.php';

// insert your API key here
$apikey = "";

// Get the Process URL
$process = CloudConvert::createProcess("png", "pdf", $apikey);
$url = $process -> getURL();
if(!empty($url)) {
?>
<html>
	<head>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	</head>
	<body>
	    <h3>Process: <?=$url ?></h3>
	    
		<label for="file">Please select a PNG file</label>
		<form action="<?=$url ?>" method="POST" enctype="multipart/form-data" id="form" target="hiddenframe">
			<input type="hidden" name="input" value="upload">
			<input type="hidden" name="format" value="pdf">
			<input type="file" name="file">
			<input type="submit">
		</form>
		
		<!-- used for uploading the file to prevent page reload -->
		<iframe frameborder="0" width="0" height="0" name="hiddenframe" id="hiddenframe"></iframe>
		
		<!-- display current status -->
		<pre id="status"></pre>										
		
		
		






		<script>
						$(document).ready(function() {

				var getstatus = function() {

					$.ajax({
						url : "<?=$url ?>
						",
						}).done(function(data) {
						// update info
						$('#status').text('Step ' + data.step + ': ' + data.message);

						// check status every second
						if (data.step != 'error' && data.step != 'finished') {
						window.setTimeout(getstatus, 1000);
						}

						// finsihed ? -> echo download URL
						if(data.step == 'finished') {
						$('#status').parent().append('<a href="' + data.output.url + '">Download file</a>');
						}

						});

						}

						$('#form').submit(function() {
						$('#status').text('Uploading...');
						});

						$('#hiddenframe').load(function() {
						// iframe loaded: file is uploaded, check the status
						getstatus();
						});

						});
		</script>

	</body>
</html>

<? } ?>