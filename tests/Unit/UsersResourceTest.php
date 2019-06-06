<?php


namespace CloudConvert\Tests\Unit;


use CloudConvert\Models\User;


use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;

class UsersResourceTest extends TestCase
{

    public function testMe()
    {


        $response = new Response(200, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/user.json'));


        $this->getMockClient()->on(new RequestMatcher('/v2/users/me', null, 'GET'), $response);


        $user = $this->cloudConvert->users()->me();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->getId());
        $this->assertEquals('Username', $user->getUsername());
        $this->assertEquals('me@example.com', $user->getEmail());
        $this->assertEquals(4434, $user->getCredits());



    }

}
