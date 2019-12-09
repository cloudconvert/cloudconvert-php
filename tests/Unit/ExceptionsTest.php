<?php


namespace CloudConvert\Tests\Unit;


use CloudConvert\Exceptions\HttpClientException;
use CloudConvert\Exceptions\HttpServerException;
use CloudConvert\Models\Job;
use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;

class ExceptionsTest extends TestCase
{

    public function testUnauthorized()
    {


        $response = new Response(401, [
            'Content-Type' => 'application/json'
        ], '{
                        "message": "Message!"
                  }');

        $this->getMockClient()->on(new RequestMatcher('/v2/users/me', null, 'GET'), $response);


        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessageRegExp("/Message!/");

        $this->cloudConvert->users()->me();

    }


    public function testValidationErrors()
    {


        $response = new Response(400, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/error400.json'));

        $this->getMockClient()->on(new RequestMatcher('/v2/jobs', null, 'POST'), $response);


        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessageRegExp("/The given data was invalid/");
        $this->expectExceptionMessageRegExp("/Cannot change status: task already completed/");

        $this->cloudConvert->jobs()->create(new Job());

    }


    public function test503()
    {


        $response = new Response(503, [
            'Content-Type' => 'text/html'
        ], 'error!');

        $this->getMockClient()->on(new RequestMatcher('/v2/users/me', null, 'GET'), $response);


        $this->expectException(HttpServerException::class);

        $this->cloudConvert->users()->me();

    }


    public function testsCreditsExceeded()
    {


        $response = new Response(402, [
            'Content-Type' => 'application/json'
        ], file_get_contents(__DIR__ . '/responses/error402.json'));

        $this->getMockClient()->on(new RequestMatcher('/v2/jobs', null, 'POST'), $response);

        try {

            $this->cloudConvert->jobs()->create(new Job());

        } catch (HttpClientException $exception) {

            $this->assertEquals('CREDITS_EXCEEDED', $exception->getErrorCode());

        }

    }

}
