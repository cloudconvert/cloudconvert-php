<?php


namespace CloudConvert\Hydrator;


use Psr\Http\Message\ResponseInterface;

interface HydratorInterface
{

    public function hydrateObject($object, $data);

    public function hydrateArray($array, string $objectClass, $data);

    public function createObjectByResponse(string $class, ResponseInterface $response);

    public function hydrateObjectByResponse($object, ResponseInterface $response);

    public function hydrateArrayByResponse($array, string $objectClass, ResponseInterface $response);

}
