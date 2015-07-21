<?php
/**
 * This file contains code about \CloudConvert\Process class
 */
namespace CloudConvert;

use CloudConvert\Exceptions\InvalidParameterException;
use GuzzleHttp\Stream\Stream;

/**
 * CloudConvert Process Wrapper
 *
 * @package CloudConvert
 * @category CloudConvert
 * @author Josias Montag <josias@montag.info>
 */
class Process extends ApiObject
{

    /**
     * Construct a new Process instance
     *
     * @param Api $api
     * @param string $url The Process URL
     * @return \CloudConvert\Process
     *
     * @throws InvalidParameterException if one parameter is missing or with bad value
     */
    public function __construct(Api $api, $url)
    {
        parent::__construct($api, $url);
        return $this;
    }

    /**
     * Starts the Process
     *
     * @param array $parameters Parameters for creating the Process. See https://cloudconvert.com/apidoc#start
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception if there is a general HTTP / network error
     *
     */

    public function start($parameters)
    {
        $this->data = $this->api->post($this->url, $parameters, false);
        return $this;
    }

    /**
     * Waits for the Process to finish (or end with an error)
     *
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception if there is a general HTTP / network error
     *
     */
    public function wait()
    {
        if ($this->step == 'finished' || $this->step == 'error') {
            return $this;
        }

        return $this->refresh(['wait' => 'true']);
    }

    /**
     * Download process files from API
     *
     * @param string $localfile Local file name (or directory) the file should be downloaded to
     * @param string $remotefile Remote file name which should be downloaded (if there are
     *         multiple output files available)
     *
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception if there is a general HTTP / network error
     * ª@throws Exceptions\InvalidParameterException
     *
     */
    public function download($localfile = null, $remotefile = null)
    {
        if (!isset($this->output->url)) {
            throw new Exceptions\ApiException("There is no output file available (yet)", 400);
        }

        if (isset($localfile) && is_dir($localfile) && isset($this->output->filename)) {
            $localfile = realpath($localfile) . DIRECTORY_SEPARATOR
                . (isset($remotefile) ? $remotefile : $this->output->filename);
        } elseif (!isset($localfile) && isset($this->output->filename)) {
            $localfile = (isset($remotefile) ? $remotefile : $this->output->filename);
        }

        if (!isset($localfile) || is_dir($localfile)) {
            throw new Exceptions\InvalidParameterException("localfile parameter is not set correctly");
        }

        $local = Stream::factory(fopen($localfile, 'w'));
        $download = $this->api->get($this->output->url . (isset($remotefile) ? '/' . $remotefile : ''), false, false);
        $local->write($download);
        return $this;
    }

    /**
     * Download all output process files from API
     *
     * @param string $directory Local directory the files should be downloaded to
     *
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception if there is a general HTTP / network error
     *
     */
    public function downloadAll($directory = null)
    {
        if (!isset($this->output->files)) { // there are not multiple output files -> do normal download
            return $this->download($directory);
        }

        foreach ($this->output->files as $file) {
            $this->download($directory, $file);
        }

        return $this;
    }


    /**
     * Delete Process from API
     *
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception if there is a general HTTP / network error
     *
     */
    public function delete()
    {
        $this->api->delete($this->url, false, false);
        return $this;
    }
}
