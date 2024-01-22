<?php


namespace CloudConvert\Transport;


use CloudConvert\CloudConvert;
use CloudConvert\Exceptions\HttpClientException;
use CloudConvert\Exceptions\HttpServerException;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\Plugin\RedirectPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;


class HttpTransport
{

    protected $options;
    protected $httpClient;


    /**
     * HttpTransport constructor.
     *
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;
        $this->httpClient = $this->createHttpClientInstance();
    }


    /**
     * Creates a new instance of the HTTP client.
     *
     * @return PluginClient
     */
    protected function createHttpClientInstance(): PluginClient
    {

        $httpClient = $this->options['http_client'] ?? Psr18ClientDiscovery::find();
        $httpClientPlugins = [
            new HeaderDefaultsPlugin([
                'User-Agent' => 'cloudconvert-php/v' . CloudConvert::VERSION . ' (https://github.com/cloudconvert/cloudconvert-php)',
            ]),
            new RedirectPlugin()
        ];

        return new PluginClient($httpClient, $httpClientPlugins);
    }


    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        if ($this->options['sandbox']) {
            return 'https://api.sandbox.cloudconvert.com/v2';
        }
        return isset($this->options['region']) ? 'https://' . $this->options['region'] . '.api.cloudconvert.com/v2' : 'https://api.cloudconvert.com/v2';
    }

    /**
     * @return string
     */
    public function getSyncBaseUri(): string
    {
        if ($this->options['sandbox']) {
            return 'https://sync.api.sandbox.cloudconvert.com/v2';
        }
        return isset($this->options['region']) ? 'https://' . $this->options['region'] . '.sync.api.cloudconvert.com/v2' : 'https://sync.api.cloudconvert.com/v2';
    }

    /**
     * @return PluginClient
     */
    public function getHttpClient(): PluginClient
    {
        return $this->httpClient;
    }

    /**
     * @return RequestFactoryInterface
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->options['request_factory'] ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * @return UriFactoryInterface
     */
    public function getUriFactory(): UriFactoryInterface
    {
        return $this->options['uri_factory'] ?? Psr17FactoryDiscovery::findUriFactory();
    }

    /**
     * @return StreamFactoryInterface
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->options['stream_factory'] ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @param       $path
     * @param array $query
     *
     * @return ResponseInterface
     * @throws \CloudConvert\Exceptions\Exception
     */
    public function get(string $path, array $query = []): ResponseInterface
    {
        if (count($query) > 0) {
            $path .= '?' . http_build_query($query);
        }


        return $this->sendRequest($this->getRequestFactory()->createRequest('GET', $path, [
            'accept-encoding' => 'application/json'
        ]));
    }


    /**
     * @param string $url
     *
     * @return StreamInterface
     */
    public function download(string $url)
    {
        return $this->sendRequest($this->getRequestFactory()->createRequest('GET', $url), false)->getBody();
    }


    /**
     * @param array<string, mixed>|string $body
     */
    protected function buildBody($body): StreamInterface
    {
        $stringBody = is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body;

        return $this->getStreamFactory()->createStream($stringBody);
    }

    /**
     * @param $path
     * @param $body
     *
     * @return ResponseInterface
     */
    public function post(string $path, array $body): ResponseInterface
    {
        return $this->sendRequest(
            $this->getRequestFactory()->createRequest('POST', $path)
                ->withHeader('content-type', 'application/json')
                ->withHeader('accept-encoding', 'application/json')
                ->withBody($this->buildBody($body))
        );
    }

    /**
     * @param $path
     * @param $body
     *
     * @return ResponseInterface
     */
    public function put(string $path, array $body): ResponseInterface
    {
        return $this->sendRequest(
            $this->getRequestFactory()->createRequest('POST', $path)
                ->withHeader('content-type', 'application/json')
                ->withHeader('accept-encoding', 'application/json')
                ->withBody($this->buildBody($body))
        );
    }

    /**
     * @param $path
     *
     * @return ResponseInterface
     */
    public function delete(string $path): ResponseInterface
    {
        return $this->sendRequest($this->getRequestFactory()->createRequest('DELETE', $path, [
            'accept-encoding' => 'application/json'
        ]));
    }

    /**
     * @param                                 $path
     * @param string|resource|StreamInterface $file
     * @param string|null                     $fileName
     * @param array                           $additionalParameters
     *
     * @return ResponseInterface
     */
    public function upload($path, $file, string $fileName = null, array $additionalParameters = []): ResponseInterface
    {
        $builder = new MultipartStreamBuilder($this->getStreamFactory());
        foreach ($additionalParameters as $parameter => $value) {
            $builder->addResource($parameter, strval($value));
        }

        $resourceOptions = [];
        if ($fileName !== null) {
            $resourceOptions['filename'] = $fileName;
        }
        $builder->addResource('file', $file, $resourceOptions);

        $multipartStream = $builder->build();
        $boundary = $builder->getBoundary();

        $request = $this->getRequestFactory()->createRequest(
            'POST',
            $path
        )
            ->withHeader('Content-Type', 'multipart/form-data; boundary="' . $boundary . '"')
            ->withBody($multipartStream);

        return $this->sendRequest($request, false);
    }

    /**
     * @param RequestInterface $request
     *
     * @param bool             $authenticate
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function sendRequest(RequestInterface $request, $authenticate = true)
    {

        try {
            if ($authenticate) {
                $request = $request->withHeader('Authorization', 'Bearer ' . $this->options['api_key']);
            }
            $response = $this->getHttpClient()->sendRequest($request);
        } catch (\Http\Client\Exception $exception) {
            throw HttpServerException::networkError($exception);
        }

        if (!in_array($response->getStatusCode(), [200, 201, 204])) {
            $this->handleErrors($response);
        }

        return $response;

    }


    /**
     * Throw the correct exception for this error.
     *
     * @throws \CloudConvert\Exceptions\Exception
     */
    protected function handleErrors(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case 400:
                throw HttpClientException::badRequest($response);
            case 401:
                throw HttpClientException::unauthorized($response);
            case 402:
                throw HttpClientException::paymentRequired($response);
            case 403:
                throw HttpClientException::forbidden($response);
            case 404:
                throw HttpClientException::notFound($response);
            case 422:
                throw HttpClientException::unprocessable($response);
            case 500 <= $statusCode:
                throw HttpServerException::serverError($statusCode);
            default:
                throw HttpServerException::unknownHttpResponseCode($statusCode);
        }
    }


}
