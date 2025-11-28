<?php

namespace Taler\Tests\Api\DonauCharity\Actions;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Api\DonauCharity\Dto\PostDonauRequest;
use Taler\Api\TwoFactorAuth\Dto\ChallengeResponse;
use Taler\Api\DonauCharity\DonauCharityClient;
use Taler\Api\DonauCharity\Actions\CreateDonauCharity;
use Taler\Exception\TalerException;

class CreateDonauCharityTest extends TestCase
{
    private DonauCharityClient $client;
    private ResponseInterface $response;
    private StreamInterface $stream;

    protected function setUp(): void
    {
        /** @var DonauCharityClient&\PHPUnit\Framework\MockObject\MockObject $client */
        $client = $this->createMock(DonauCharityClient::class);
        $this->client = $client;

        /** @var ResponseInterface&\PHPUnit\Framework\MockObject\MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        $this->response = $response;

        /** @var StreamInterface&\PHPUnit\Framework\MockObject\MockObject $stream */
        $stream = $this->createMock(StreamInterface::class);
        $this->stream = $stream;
    }

    public function testRunSuccess204(): void
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $this->client->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->client->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->client->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $dto = new PostDonauRequest('https://donau.example', 7);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'private/donau',
                [],
                $this->isType('string')
            )
            ->willReturn($this->response);

        $result = CreateDonauCharity::run($this->client, $dto);
        $this->assertNull($result);
    }

    public function testRun202Challenge(): void
    {
        $challengeData = [
            'challenges' => [
                [
                    'challenge_id' => 'abc-123',
                    'tan_channel' => 'sms',
                    'tan_info' => '***999',
                ],
            ],
            'combi_and' => true,
        ];

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(202);

        $this->client->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->client->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->client->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 202)
            ->willReturn($challengeData);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->client->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $dto = new PostDonauRequest('https://donau.example', 7);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'private/donau',
                [],
                $this->isType('string')
            )
            ->willReturn($this->response);

        $result = CreateDonauCharity::run($this->client, $dto);
        $this->assertInstanceOf(ChallengeResponse::class, $result);
        $this->assertCount(1, $result->challenges);
        $this->assertTrue($result->combi_and);
        $this->assertSame('abc-123', $result->challenges[0]->challenge_id);
    }

    public function testUnexpectedStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(500);

        $this->client->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->client->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->client->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 204)
            ->willThrowException(new TalerException('Server error', 500));

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->client->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $dto = new PostDonauRequest('https://donau.example', 7);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'private/donau',
                [],
                $this->isType('string')
            )
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        CreateDonauCharity::run($this->client, $dto);
    }

    public function testRunAsync(): void
    {
        $promise = $this->createMock(\GuzzleHttp\Promise\PromiseInterface::class);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->client->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $dto = new PostDonauRequest('https://donau.example', 7);

        $httpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'POST',
                'private/donau',
                [],
                $this->isType('string')
            )
            ->willReturn($promise);

        $result = CreateDonauCharity::runAsync($this->client, $dto);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
    }

    public function testJsonEncoding(): void
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $this->client->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->client->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->client->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $dto = new PostDonauRequest('https://donau.example', 7);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'private/donau',
                [],
                $this->callback(function ($jsonString) {
                    $data = json_decode($jsonString, true);
                    return isset($data['donau_url'], $data['charity_id']) &&
                        $data['donau_url'] === 'https://donau.example' &&
                        $data['charity_id'] === 7;
                })
            )
            ->willReturn($this->response);

        CreateDonauCharity::run($this->client, $dto);
        $this->assertTrue(true);
    }
}



