<?php

namespace Taler\Tests\Api\TwoFactorAuth;

use Http\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Api\Dto\Timestamp;
use Taler\Api\TwoFactorAuth\Dto\ChallengeRequestResponse;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;
use Taler\Api\TwoFactorAuth\TwoFactorAuthClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

final class TwoFactorAuthClientTest extends TestCase
{
    private TwoFactorAuthClient $client;
    private HttpClientWrapper&MockObject $httpClient;
    private Taler&MockObject $taler;
    private ResponseInterface&MockObject $response;
    private StreamInterface&MockObject $stream;
    private Promise&MockObject $promise;
    private TalerConfig&MockObject $config;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientWrapper::class);
        $this->taler = $this->createMock(Taler::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);
        $this->promise = $this->createMock(Promise::class);
        $this->config = $this->createMock(TalerConfig::class);

        $this->taler->method('getConfig')->willReturn($this->config);
        $this->config->method('getWrapResponse')->willReturn(true);
        $this->promise->method('then')->willReturnSelf();

        $this->client = new TwoFactorAuthClient($this->taler, $this->httpClient);
    }

    public function testRequestChallenge(): void
    {
        $data = [
            'solve_expiration' => ['t_s' => 1700100000],
            'earliest_retransmission' => ['t_s' => 1700100300],
        ];

        $this->stream->method('__toString')->willReturn(json_encode($data));
        $this->response->method('getBody')->willReturn($this->stream);
        $this->response->method('getStatusCode')->willReturn(200);
        $this->httpClient->method('request')->willReturn($this->response);

        $result = $this->client->requestChallenge('inst1', 'ch-1');
        $this->assertInstanceOf(ChallengeRequestResponse::class, $result);
        $this->assertInstanceOf(Timestamp::class, $result->solve_expiration);
        $this->assertInstanceOf(Timestamp::class, $result->earliest_retransmission);
        $this->assertSame(1700100000, $result->solve_expiration->t_s);
        $this->assertSame(1700100300, $result->earliest_retransmission->t_s);
    }

    public function testRequestChallengeAsync(): void
    {
        $data = [
            'solve_expiration' => ['t_s' => 1700200000],
            'earliest_retransmission' => ['t_s' => 1700200300],
        ];

        $this->stream->method('__toString')->willReturn(json_encode($data));
        $this->response->method('getBody')->willReturn($this->stream);
        $this->response->method('getStatusCode')->willReturn(200);
        $promise = new \GuzzleHttp\Promise\Promise();
        $this->httpClient->method('requestAsync')->willReturn($promise);

        $result = $this->client->requestChallengeAsync('inst1', 'ch-2');
        $promise->resolve($this->response);

        $this->assertInstanceOf(ChallengeRequestResponse::class, $result->wait());
        $this->assertSame(1700200000, $result->wait()->solve_expiration->t_s);
        $this->assertSame(1700200300, $result->wait()->earliest_retransmission->t_s);
    }

    public function testConfirmChallenge(): void
    {
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);
        $this->response->method('getStatusCode')->willReturn(204);
        $this->httpClient->method('request')->willReturn($this->response);

        $req = new MerchantChallengeSolveRequest('654321');
        $this->client->confirmChallenge('inst1', 'ch-3', $req);
        $this->addToAssertionCount(1);
    }

    public function testConfirmChallengeAsync(): void
    {
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);
        $this->response->method('getStatusCode')->willReturn(204);

        $promise = new \GuzzleHttp\Promise\Promise();
        $this->httpClient->method('requestAsync')->willReturn($promise);

        $req = new MerchantChallengeSolveRequest('999999');
        $result = $this->client->confirmChallengeAsync('inst1', 'ch-4', $req);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}


