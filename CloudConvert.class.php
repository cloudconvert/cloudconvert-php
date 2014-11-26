<?php
/*
 * CloudConvert API Example Class
 * 2013 by Lunaweb Ltd.
 * Feel free to use, modify or publish it.
 */
class CloudConvert {

    private $apikey;
    private $url;
    private $data;
    private $options = array();

    /*
     * creates new Process ID.
     * see: https://cloudconvert.org/page/api#start
     *
     */

    public static function createProcess($inputformat, $outputformat, $apikey = null) {

        $instance = new self();

        $instance -> apikey = $apikey;

        $data = $instance -> req('https://api.cloudconvert.com/process', array(
            'inputformat' => $inputformat,
            'outputformat' => $outputformat,
            'apikey' => $apikey
        ));

        if (strpos($data -> url, 'http') === false)
            $data -> url = "https:" . $data -> url;

        $instance -> url = $data -> url;

        return $instance;

    }

    /*
     * uses existing Process by given Process URL
     */
    public static function useProcess($url) {
        $instance = new self();
        if (strpos($url, 'http') === false)
            $url = "https:" . $url;
        $instance -> url = $url;
        return $instance;
    }

    /*
     * Set conversion option.
     * examples:
     * $converter -> setOption('email', '1');
     * $converter -> setOption('options[audio_bitrate]', '128');
     *
     */

    public function setOption($name, $val) {
        $this -> options[$name] = $val;
    }

    /*
     * Uploads the input file (server side)
     */
    public function upload($filepath, $outputformat) {
        $this -> req($this -> url, array_merge(array(
            'input' => 'upload',
            'format' => $outputformat,
            'file' => (class_exists('CURLFile') ? new CURLFile($filepath) : '@' . $filepath . ';filename=' . basename($filepath)) // CURLFile is available PHP >= 5.5.0
        ), $this -> options));
    }

    /*
     * Let CloudConvert download the input file by a given URL and filename
     */
    public function uploadByURL($url, $filename, $outputformat) {
        $this -> req($this -> url, array_merge(array(
            'input' => 'download',
            'format' => $outputformat,
            'filename' => $filename,
            'link' => $url,
        ), $this -> options));

    }

    /*
     * returns Process URL
     */
    public function getURL() {
        return $this -> url;
    }

    /*
     * Checks the current status of the process
     */
    public function status($action = null) {
        if (empty($this -> url))
            throw new Exception("No process URL found! (Conversion not started)");
        $this -> data = $this -> req($this -> url . ($action ? '/' . $action : ''));
        return $this -> data;
    }

    public function cancel() {
        return $this -> status('cancel');
    }

    public function delete() {
        return $this -> status('delete');
    }

    /*
     * Blocks until the conversion is finished
     */
    public function waitForConversion($timeout = 120) {
        $time = 0;
        /*
         * Check the status every second, up to timeout
         */
        while ($time <= $timeout) {
            sleep(1);
            $time++;
            $data = $this -> status();
            if ($data -> step == 'error') {
                throw new Exception($data -> message);
                return false;
            } elseif ($data -> step == 'finished' && isset($data -> output) && isset($data -> output -> url)) {
                return true;
            }
        }
        throw new Exception('Timeout');
        return false;
    }

    /*
     * Download output file to local target
     */
    public function download($target) {
        if (empty($this -> data -> output -> url))
            throw new Exception("No download URL found! (Conversion not finished or failed)");
        if (strpos($this -> data -> output -> url, 'http') === false)
            $this -> data -> output -> url = "https:" . $this -> data -> output -> url;
        $fp = fopen($target, 'w+');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this -> data -> output -> url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        if (!curl_exec($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        fclose($fp);
    }
    
    /*
     * Return output stream to variable
     */
    public function downloadStream() {
        if (empty($this -> data -> output -> url))
            throw new Exception("No download URL found! (Conversion not finished or failed)");
        if (strpos($this -> data -> output -> url, 'http') === false)
            $this -> data -> output -> url = "https:" . $this -> data -> output -> url;

	$ch = curl_init();	
		curl_setopt_array($ch, array(		
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HEADER  => false,
		CURLOPT_HTTPGET => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_URL => $this -> data -> output -> url
	));

	$stream = curl_exec($ch);
	curl_close($ch);
	
	return $stream;			
    }

    private function req($url, $post = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);

        /*
         * If you have SSL cert errors, try to disable SSL verifyer.
         */
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }


        $return = curl_exec($ch);

        if ($return === FALSE) {
            throw new Exception(curl_error($ch));
        } else {
            $json = json_decode($return);
            if (isset($json -> error))
                throw new Exception($json -> error);
            return $json;
        }
        curl_close($ch);

    }

}
?>
