<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Api\Instance\Actions\CreateInstance;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\Dto\InstanceConfigurationMessage;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;
use Taler\Api\TwoFactorAuth\TwoFactorAuthClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

final class CreateInstanceTwoFactorAuthFlowTest extends TestCase
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

    private function createConfig(string $instanceId): InstanceConfigurationMessage
    {
        $auth = new InstanceAuthConfigToken('test-password');
        $address = new Location(country: 'DE', town: 'Berlin');
        $jurisdiction = new Location(country: 'DE', town: 'Berlin');
        $wireTransferDelay = new RelativeTime(d_us: 86400000000);
        $payDelay = new RelativeTime(d_us: 3600000000);

        return new InstanceConfigurationMessage(
            id: $instanceId,
            name: 'Test Instance',
            email: 'test@example.com',
            phone_number: '+49123456789',
            website: 'https://example.com',
            logo: 'https://example.com/logo.png',
            auth: $auth,
            address: $address,
            jurisdiction: $jurisdiction,
            use_stefan: true,
            default_wire_transfer_delay: $wireTransferDelay,
            default_pay_delay: $payDelay
        );
    }

    public function testCreateInstanceTwoFactorFlowSuccess(): void
    {
        $instanceId = 'inst-2fa-success';
        $config = $this->createConfig($instanceId);

        /** @var ResponseInterface&MockObject $responseChallenge */
        $responseChallenge = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamChallenge */
        $streamChallenge = $this->createMock(StreamInterface::class);
        $responseChallenge->method('getStatusCode')->willReturn(202);
        $streamChallenge->method('__toString')->willReturn(json_encode([
            'challenges' => [
                [
                    'challenge_id' => 'ch-inst-success',
                    'tan_channel' => 'sms',
                    'tan_info' => '***1234',
                ],
            ],
            'combi_and' => true,
        ]));
        $responseChallenge->method('getBody')->willReturn($streamChallenge);

        /** @var ResponseInterface&MockObject $responseRequest */
        $responseRequest = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamRequest */
        $streamRequest = $this->createMock(StreamInterface::class);
        $responseRequest->method('getStatusCode')->willReturn(200);
        $streamRequest->method('__toString')->willReturn(json_encode([
            'solve_expiration' => ['t_s' => 1700100000],
            'earliest_retransmission' => ['t_s' => 1700100300],
        ]));
        $responseRequest->method('getBody')->willReturn($streamRequest);

        /** @var ResponseInterface&MockObject $responseConfirm */
        $responseConfirm = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamConfirm */
        $streamConfirm = $this->createMock(StreamInterface::class);
        $responseConfirm->method('getStatusCode')->willReturn(204);
        $streamConfirm->method('__toString')->willReturn('');
        $responseConfirm->method('getBody')->willReturn($streamConfirm);

        /** @var ResponseInterface&MockObject $responseRepeat */
        $responseRepeat = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamRepeat */
        $streamRepeat = $this->createMock(StreamInterface::class);
        $responseRepeat->method('getStatusCode')->willReturn(204);
        $streamRepeat->method('__toString')->willReturn('');
        $responseRepeat->method('getBody')->willReturn($streamRepeat);

        $step = 0;
        $capturedEndpoint = null;
        $capturedHeaders = null;
        $capturedBody = null;
        $self = $this;

        $this->httpClient
            ->method('request')
            ->willReturnCallback(
                function (string $method, string $endpoint, array $headers = [], ?string $body = null) use (
                    &$step,
                    &$capturedEndpoint,
                    &$capturedHeaders,
                    &$capturedBody,
                    $self,
                    $instanceId,
                    $responseChallenge,
                    $responseRequest,
                    $responseConfirm,
                    $responseRepeat
                ) {
                    switch ($step) {
                        case 0:
                            $self->assertSame('POST', $method);
                            $self->assertSame('instances', $endpoint);
                            $self->assertSame([], $headers);
                            $self->assertIsString($body);
                            $capturedEndpoint = $endpoint;
                            $capturedHeaders = $headers;
                            $capturedBody = $body;
                            $step++;
                            return $responseChallenge;
                        case 1:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-inst-success", $endpoint);
                            $self->assertIsString($body);
                            $step++;
                            return $responseRequest;
                        case 2:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-inst-success/confirm", $endpoint);
                            $self->assertIsString($body);
                            $decoded = json_decode($body, true);
                            $self->assertSame('123456', $decoded['tan'] ?? null);
                            $step++;
                            return $responseConfirm;
                        case 3:
                            $self->assertSame('POST', $method);
                            $self->assertSame($capturedEndpoint, $endpoint, 'Repeated endpoint must match original exactly');
                            $self->assertIsString($body);
                            $self->assertSame($capturedBody, $body, 'Repeated request body must match original exactly');
                            $expectedHeaders = $capturedHeaders;
                            $expectedHeaders['Taler-Challenge-Ids'] = 'ch-inst-success';
                            ksort($expectedHeaders);
                            $sortedActual = $headers;
                            ksort($sortedActual);
                            $self->assertSame($expectedHeaders, $sortedActual);
                            $step++;
                            return $responseRepeat;
                        default:
                            $self->fail('Unexpected additional HTTP request in CreateInstance 2FA flow success test');
                    }
                }
            );

        // 1) Protected instance creation -> ChallengeResponse
        $challengeResponse = CreateInstance::run($this->instanceClient, $config);

        $this->assertInstanceOf(\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse::class, $challengeResponse);
        $this->assertCount(1, $challengeResponse->challenges);
        $challengeId = $challengeResponse->challenges[0]->challenge_id;
        $this->assertSame('ch-inst-success', $challengeId);

        // 2) Request TAN
        $this->twoFaClient->requestChallenge($instanceId, $challengeId);

        // 3) Confirm TAN
        $this->twoFaClient->confirmChallenge(
            $instanceId,
            $challengeId,
            new MerchantChallengeSolveRequest('123456')
        );

        // 4) Repeat instance creation with Taler-Challenge-Ids header
        $result = CreateInstance::run(
            $this->instanceClient,
            $config,
            ['Taler-Challenge-Ids' => $challengeId]
        );

        $this->assertNull($result);
        $this->assertSame(4, $step, 'Expected exactly 4 HTTP calls in success 2FA flow');
    }

    public function testCreateInstanceTwoFactorFlowFailsWithInvalidTan(): void
    {
        $instanceId = 'inst-2fa-fail';
        $config = $this->createConfig($instanceId);

        /** @var ResponseInterface&MockObject $responseChallenge */
        $responseChallenge = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamChallenge */
        $streamChallenge = $this->createMock(StreamInterface::class);
        $responseChallenge->method('getStatusCode')->willReturn(202);
        $streamChallenge->method('__toString')->willReturn(json_encode([
            'challenges' => [
                [
                    'challenge_id' => 'ch-inst-fail',
                    'tan_channel' => 'sms',
                    'tan_info' => '***5678',
                ],
            ],
            'combi_and' => true,
        ]));
        $responseChallenge->method('getBody')->willReturn($streamChallenge);

        /** @var ResponseInterface&MockObject $responseRequest */
        $responseRequest = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamRequest */
        $streamRequest = $this->createMock(StreamInterface::class);
        $responseRequest->method('getStatusCode')->willReturn(200);
        $streamRequest->method('__toString')->willReturn(json_encode([
            'solve_expiration' => ['t_s' => 1700200000],
            'earliest_retransmission' => ['t_s' => 1700200300],
        ]));
        $responseRequest->method('getBody')->willReturn($streamRequest);

        /** @var ResponseInterface&MockObject $responseConfirmFail */
        $responseConfirmFail = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamConfirmFail */
        $streamConfirmFail = $this->createMock(StreamInterface::class);
        $responseConfirmFail->method('getStatusCode')->willReturn(400);
        $streamConfirmFail->method('__toString')->willReturn(json_encode([
            'code' => 4000,
            'hint' => 'Invalid TAN',
        ]));
        $responseConfirmFail->method('getBody')->willReturn($streamConfirmFail);

        $step = 0;
        $self = $this;

        $this->httpClient
            ->method('request')
            ->willReturnCallback(
                function (string $method, string $endpoint, array $headers = [], ?string $body = null) use (
                    &$step,
                    $self,
                    $instanceId,
                    $responseChallenge,
                    $responseRequest,
                    $responseConfirmFail
                ) {
                    switch ($step) {
                        case 0:
                            $self->assertSame('POST', $method);
                            $self->assertSame('instances', $endpoint);
                            $self->assertSame([], $headers);
                            $self->assertIsString($body);
                            $step++;
                            return $responseChallenge;
                        case 1:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-inst-fail", $endpoint);
                            $self->assertIsString($body);
                            $step++;
                            return $responseRequest;
                        case 2:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-inst-fail/confirm", $endpoint);
                            $self->assertIsString($body);
                            $decoded = json_decode($body, true);
                            $self->assertSame('000000', $decoded['tan'] ?? null);
                            $step++;
                            return $responseConfirmFail;
                        default:
                            $self->fail('Unexpected additional HTTP request in CreateInstance 2FA flow failure test');
                    }
                }
            );

        $challengeResponse = CreateInstance::run($this->instanceClient, $config);

        $this->assertInstanceOf(\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse::class, $challengeResponse);
        $this->assertCount(1, $challengeResponse->challenges);
        $challengeId = $challengeResponse->challenges[0]->challenge_id;
        $this->assertSame('ch-inst-fail', $challengeId);

        $this->twoFaClient->requestChallenge($instanceId, $challengeId);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Unexpected response status code: 400');

        $this->twoFaClient->confirmChallenge(
            $instanceId,
            $challengeId,
            new MerchantChallengeSolveRequest('000000')
        );

        $this->assertSame(3, $step, 'Expected exactly 3 HTTP calls before failure in 2FA flow');
    }
}



