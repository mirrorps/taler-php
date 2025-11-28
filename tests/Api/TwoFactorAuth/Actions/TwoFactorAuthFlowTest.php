<?php

namespace Taler\Tests\Api\TwoFactorAuth\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;
use Taler\Api\TwoFactorAuth\TwoFactorAuthClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

final class TwoFactorAuthFlowTest extends TestCase
{
    private HttpClientWrapper&MockObject $httpClient;
    private Taler $taler;
    private InstanceClient $instanceClient;
    private TwoFactorAuthClient $twoFaClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientWrapper::class);
        $this->taler = new Taler(new TalerConfig('https://example.com', '', true));
        $this->instanceClient = new InstanceClient($this->taler, $this->httpClient);
        $this->twoFaClient = new TwoFactorAuthClient($this->taler, $this->httpClient);
    }

    public function testProtectedEndpoint2faFlowWithExactReplay(): void
    {
        $instanceId = 'inst1';
        $authConfig = new InstanceAuthConfigToken('new-secret');

        // Responses and streams for each step
        $responseChallenge = $this->createMock(ResponseInterface::class);
        $streamChallenge = $this->createMock(StreamInterface::class);
        $responseChallenge->method('getStatusCode')->willReturn(202);
        $streamChallenge->method('__toString')->willReturn(json_encode([
            'challenges' => [
                [
                    'challenge_id' => 'ch-xyz',
                    'tan_channel' => 'sms',
                    'tan_info' => '***1234',
                ],
            ],
            'combi_and' => true,
        ]));
        $responseChallenge->method('getBody')->willReturn($streamChallenge);

        $responseRequest = $this->createMock(ResponseInterface::class);
        $streamRequest = $this->createMock(StreamInterface::class);
        $responseRequest->method('getStatusCode')->willReturn(200);
        $streamRequest->method('__toString')->willReturn(json_encode([
            'solve_expiration' => ['t_s' => 1700100000],
            'earliest_retransmission' => ['t_s' => 1700100300],
        ]));
        $responseRequest->method('getBody')->willReturn($streamRequest);

        $responseConfirm = $this->createMock(ResponseInterface::class);
        $streamConfirm = $this->createMock(StreamInterface::class);
        $responseConfirm->method('getStatusCode')->willReturn(204);
        $streamConfirm->method('__toString')->willReturn('');
        $responseConfirm->method('getBody')->willReturn($streamConfirm);

        $responseRepeat = $this->createMock(ResponseInterface::class);
        $streamRepeat = $this->createMock(StreamInterface::class);
        $responseRepeat->method('getStatusCode')->willReturn(204);
        $streamRepeat->method('__toString')->willReturn('');
        $responseRepeat->method('getBody')->willReturn($streamRepeat);

        // Track calls and capture original body
        $step = 0;
        $capturedBody = null;
        $self = $this;

        $this->httpClient
            ->method('request')
            ->willReturnCallback(function (string $method, string $endpoint, array $headers = [], ?string $body = null)
                use (&$step, &$capturedBody, $self, $instanceId, $responseChallenge, $responseRequest, $responseConfirm, $responseRepeat) {
                // Normalize endpoint to allow exact matching
                switch ($step) {
                    case 0: // initial protected call requiring 2FA
                        $self->assertSame('POST', $method);
                        $self->assertSame("instances/{$instanceId}/private/auth", $endpoint);
                        $self->assertSame([], $headers);
                        $self->assertIsString($body);
                        $capturedBody = $body;
                        $step++;
                        return $responseChallenge;
                    case 1: // request challenge TAN
                        $self->assertSame('POST', $method);
                        $self->assertSame("instances/{$instanceId}/challenge/ch-xyz", $endpoint);
                        $self->assertIsString($body);
                        $step++;
                        return $responseRequest;
                    case 2: // confirm challenge TAN
                        $self->assertSame('POST', $method);
                        $self->assertSame("instances/{$instanceId}/challenge/ch-xyz/confirm", $endpoint);
                        $self->assertIsString($body);
                        $decoded = json_decode($body, true);
                        $self->assertSame('123456', $decoded['tan'] ?? null);
                        $step++;
                        return $responseConfirm;
                    case 3: // repeat original protected call with exact same body and header
                        $self->assertSame('POST', $method);
                        $self->assertSame("instances/{$instanceId}/private/auth", $endpoint);
                        $self->assertIsString($body);
                        $self->assertSame($capturedBody, $body, 'Repeated request body must match original exactly');
                        $self->assertArrayHasKey('Taler-Challenge-Ids', $headers);
                        $self->assertSame('ch-xyz', $headers['Taler-Challenge-Ids']);
                        $step++;
                        return $responseRepeat;
                    default:
                        $self->fail('Unexpected additional HTTP request in 2FA flow test');
                }
            });

        // 1) Protected call -> returns ChallengeResponse (202)
        $challengeResponse = \Taler\Api\Instance\Actions\UpdateAuth::run($this->instanceClient, $instanceId, $authConfig);
        $this->assertInstanceOf(\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse::class, $challengeResponse);
        $this->assertCount(1, $challengeResponse->challenges);
        $challengeId = $challengeResponse->challenges[0]->challenge_id;
        $this->assertSame('ch-xyz', $challengeId);

        // 2) Request TAN for challenge
        $this->twoFaClient->requestChallenge($instanceId, $challengeId);

        // 3) Confirm TAN
        $this->twoFaClient->confirmChallenge($instanceId, $challengeId, new MerchantChallengeSolveRequest('123456'));

        // 4) Repeat original protected call with header and exact body
        $result = \Taler\Api\Instance\Actions\UpdateAuth::run(
            $this->instanceClient,
            $instanceId,
            $authConfig,
            ['Taler-Challenge-Ids' => $challengeId]
        );
        $this->assertNull($result);
        $this->assertSame(4, $step, 'Expected exactly 4 HTTP calls in flow');
    }
}


