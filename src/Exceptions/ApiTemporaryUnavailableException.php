<?php
namespace CloudConvert\Exceptions;


/**
 * ApiBadRequestException exception is throwned when a the CloudConvert API returns any HTTP error code 503
 *
 * @package CloudConvert
 * @category Exceptions
 * @author Josias Montag <josias@montag.info>
 */
class ApiTemporaryUnavailableException extends ApiException
{
    public $retryAfter = 0;

    /**
     * @param string $msg
     * @param int $code
     * @param int $retryAfter
     */
    public function __construct($msg, $code, $retryAfter = 0)
    {
        $this->retryAfter = $retryAfter;
        return parent::__construct($msg, $code);
    }
}
