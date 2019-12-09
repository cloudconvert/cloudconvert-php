<?php


namespace CloudConvert\Exceptions;


use Psr\Http\Message\ResponseInterface;

class HttpClientException extends Exception
{
    /**
     * @var ResponseInterface|null
     */
    protected $response;
    /**
     * @var array
     */
    protected $responseBody;
    /**
     * @var int
     */
    protected $responseCode;

    /**
     * @var string|null
     */
    protected $errorCode;

    /**
     * HttpClientException constructor.
     *
     * @param string            $message
     * @param int               $code
     * @param ResponseInterface $response
     */
    public function __construct(string $message, int $code, ResponseInterface $response)
    {
        $this->response = $response;
        $this->responseCode = $response->getStatusCode();
        $this->responseBody = @json_decode($response->getBody(), true) ?? [];
        $this->errorCode = isset($this->responseBody['code']) ? $this->responseBody['code'] : null;

        if (isset($this->responseBody['message'])) {
            $message = $this->responseBody['message'];
        }

        if (isset($this->getResponseBody()['errors'])) {
            $message = $this->getMessage();
            foreach ($this->getResponseBody()['errors'] as $field => $errors) {
                $message .= ' ' . $field . ': ' . implode(' ', $errors);
            }
        }

        parent::__construct($message, $code);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return HttpClientException
     */
    public static function badRequest(ResponseInterface $response)
    {
        return new self('Invalid data.', 400, $response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return HttpClientException
     */
    public static function unauthorized(ResponseInterface $response)
    {
        return new self('Unauthorized.', 401, $response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return HttpClientException
     */
    public static function paymentRequired(ResponseInterface $response)
    {
        return new self('Credits used up.', 402, $response);
    }


    /**
     * @param ResponseInterface $response
     *
     * @return HttpClientException
     */
    public static function forbidden(ResponseInterface $response)
    {
        return new self('Forbidden.', 403, $response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return HttpClientException
     */
    public static function unprocessable(ResponseInterface $response)
    {
        return new self('Unprocessable.', 422, $response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return HttpClientException
     */
    public static function notFound(ResponseInterface $response)
    {
        return new self('The endpoint you have tried to access does not exist. ',
            404, $response);
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getResponseBody(): array
    {
        return $this->responseBody;
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }


}
