<?php


namespace CloudConvert\Resources;


use CloudConvert\Hydrator\HydratorInterface;
use CloudConvert\Transport\HttpTransport;

abstract class AbstractResource
{

    /**
     * @var HttpTransport
     */
    protected $httpTransport;
    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * AbstractResource constructor.
     *
     * @param HttpTransport     $httpTransport
     * @param HydratorInterface $hydrator
     */
    public function __construct(HttpTransport $httpTransport, HydratorInterface $hydrator)
    {
        $this->httpTransport = $httpTransport;
        $this->hydrator = $hydrator;
    }


}
