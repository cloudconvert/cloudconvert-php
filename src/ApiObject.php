<?php
/**
 * This file contains code about \CloudConvert\ApiObject class
 */
namespace CloudConvert;

/**
 * Base class for Objects returned from the CloudConvert API
 *
 * @package CloudConvert
 * @category CloudConvert
 * @author Josias Montag <josias@montag.info>
 */
class ApiObject
{
    /*
     ** The \CloudConvert\Api instance
     */
    protected $api;
    /*
     * The Object URL
     */
    public $url;

    /*
     * Contains the object data returned from the CloudConvert API
     */
    protected $data = array();

    /**
     * Construct a new ApiObject instance
     *
     * @param $api The \CloudConvert\Api instance
     * @param $url The Object URL
     *
     * @throws InvalidParameterException if one parameter is missing or with bad value
     */
    public function __construct(Api $api, $url)
    {
        if (!isset($api)) {
            throw new Exceptions\InvalidParameterException("API parameter is not set");
        }
        if (!isset($url)) {
            throw new Exceptions\InvalidParameterException("Object URL parameter is not set");
        }
        $this->api = $api;
        $this->url = $url;
        return $this;
    }

    /**
     * Refresh Object Data
     *
     * @param array $parameters Parameters for refreshing the Object.
     *
     * @return \CloudConvert\ApiObject
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception if there is a general HTTP / network error
     *
     */
    public function refresh($parameters = null)
    {
        $this->data = $this->api->get($this->url, $parameters, false);
        return $this;
    }

    /**
     * Access Object data via $object->prop->subprop
     */
    public function __get($name)
    {

        if (is_array($this->data) && array_key_exists($name, $this->data)) {
            return self::arrayToObject($this->data[$name]);
        }

        return null;
    }

    /**
     * Converts multi dimensional arrays into objects
     */
    private static function arrayToObject($d)
    {
        if (is_array($d)) {
            /*
             * Return array converted to object
             * Using [__CLASS__, __METHOD__] (Magic constant)
             * for recursive call
             */
            return (object) array_map([__CLASS__, __METHOD__], $d);
        } else {
            // Return object
            return $d;
        }
    }
}
