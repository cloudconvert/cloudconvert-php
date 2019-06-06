<?php


namespace CloudConvert\Resources;


use CloudConvert\Models\User;

class UsersResource extends AbstractResource
{

    /**
     * @return User
     * @throws \Http\Client\Exception
     */
    public function me(): User
    {

        $response = $this->httpTransport->get($this->httpTransport->getBaseUri() . '/users/me');
        return $this->hydrator->createObjectByResponse(User::class, $response);

    }


}
