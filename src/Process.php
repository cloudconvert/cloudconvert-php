<?php
/**
 * This file contains code about \CloudConvert\Process class
 */
namespace CloudConvert;

use CloudConvert\Exceptions\InvalidParameterException;

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
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */

    public function start($parameters)
    {
        if (isset($parameters['file']) && gettype($parameters['file']) == 'resource') {
            $file = $parameters['file'];
            unset($parameters['file']);
            if (isset($parameters['wait']) && $parameters['wait']) {
                unset($parameters['wait']);
                $wait = true;
            }
        }
        $this->data = $this->api->post($this->url, $parameters, false);
        if (isset($file)) {
            $this->upload($file);
        }
        if (isset($wait)) {
            $this->wait();
        }
        return $this;
    }

    /**
     * Uploads the input file. See https://cloudconvert.com/apidoc#upload
     *
     * @param string $filename Filename of the input file
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */

    public function upload($stream, $filename = null)
    {
        if (!isset($this->upload->url)) {
            throw new Exceptions\ApiException("Upload is not allowed in this process state", 400);
        }

        if (empty($filename)) {
            $metadata = stream_get_meta_data($stream);
            $filename = basename($metadata['uri']);
        }
        $this->api->put($this->upload->url . "/" . rawurlencode($filename), $stream, false);
        return $this;
    }

    /**
     * Waits for the Process to finish (or end with an error)
     *
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
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
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     * @throws Exceptions\InvalidParameterException
     *
     */
    public function download($localfile = null, $remotefile = null)
    {
        if (isset($localfile) && is_dir($localfile) && isset($this->output->filename)) {
            $localfile = realpath($localfile) . DIRECTORY_SEPARATOR
                . (isset($remotefile) ? $remotefile : $this->output->filename);
        } elseif (!isset($localfile) && isset($this->output->filename)) {
            $localfile = (isset($remotefile) ? $remotefile : $this->output->filename);
        }

        if (!isset($localfile) || is_dir($localfile)) {
            throw new Exceptions\InvalidParameterException("localfile parameter is not set correctly");
        }

        return $this->downloadStream(fopen($localfile, 'w'), $remotefile);
    }

    /**
     * Download process files from API and write to a given stream
     *
     * @param resource $stream Stream to write the downloaded data to
     * @param string $remotefile Remote file name which should be downloaded (if there are
     *         multiple output files available)
     *
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function downloadStream($stream, $remotefile = null)
    {
        if (!isset($this->output->url)) {
            throw new Exceptions\ApiException("There is no output file available (yet)", 400);
        }

        $local = \GuzzleHttp\Psr7\stream_for($stream);
        $download = $this->api->get($this->output->url . (isset($remotefile) ? '/' . rawurlencode($remotefile) : ''), false, false);
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
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function downloadAll($directory = null)
    {
        if (!isset($this->output->files)) { // the are not multiple output files -> do normal downloader
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
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function delete()
    {
        $this->api->delete($this->url, false, false);
        return $this;
    }
}
