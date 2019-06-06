<?php


namespace CloudConvert\Tests\Integration;


use CloudConvert\Models\User;


class UserTest extends TestCase
{

    public function testMe()
    {

        $user = $this->cloudConvert->users()->me();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->getId());
        $this->assertNotNull($user->getUsername());
        $this->assertNotNull($user->getEmail());
        $this->assertNotNull($user->getCredits());


    }

}
