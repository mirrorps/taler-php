<?php

namespace Taler\Tests\Api\BankAccounts\Actions;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\AccountAddDetails;
use Taler\Api\BankAccounts\Dto\AccountAddResponse;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;
use Taler\Api\TwoFactorAuth\TwoFactorAuthClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

final class CreateAccountTwoFactorAuthFlowTest extends TestCase
{
    private HttpClientWrapper&MockObject $httpClient;
    private Taler $taler;
    private BankAccountClient $bankClient;
    private TwoFactorAuthClient $twoFaClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientWrapper::class);
        $this->taler = new Taler(new TalerConfig('https://example.com', '', true));

        $this->bankClient = new BankAccountClient($this->taler, $this->httpClient);
        $this->twoFaClient = new TwoFactorAuthClient($this->taler, $this->httpClient);
    }

    public function testCreateAccountTwoFactorFlowSuccess(): void
    {
        $instanceId = 'inst1';
        $details = new AccountAddDetails('payto://iban/DE123');

        /** @var ResponseInterface&MockObject $responseChallenge */
        $responseChallenge = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamChallenge */
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
        $responseRepeat->method('getStatusCode')->willReturn(200);
        $streamRepeat->method('__toString')->willReturn(json_encode([
            'h_wire' => 'hw',
            'salt' => 's',
        ]));
        $responseRepeat->method('getBody')->willReturn($streamRepeat);

        $step = 0;
        $capturedBody = null;
        $self = $this;

        $this->httpClient
            ->method('request')
            ->willReturnCallback(
                function (string $method, string $endpoint, array $headers = [], ?string $body = null) use (
                    &$step,
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
                            $self->assertSame('private/accounts', $endpoint);
                            $self->assertSame([], $headers);
                            $self->assertIsString($body);
                            $capturedBody = $body;
                            $step++;
                            return $responseChallenge;
                        case 1:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-xyz", $endpoint);
                            $self->assertIsString($body);
                            $step++;
                            return $responseRequest;
                        case 2:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-xyz/confirm", $endpoint);
                            $self->assertIsString($body);
                            $decoded = json_decode($body, true);
                            $self->assertSame('123456', $decoded['tan'] ?? null);
                            $step++;
                            return $responseConfirm;
                        case 3:
                            $self->assertSame('POST', $method);
                            $self->assertSame('private/accounts', $endpoint);
                            $self->assertIsString($body);
                            $self->assertSame($capturedBody, $body);
                            $self->assertArrayHasKey('Taler-Challenge-Ids', $headers);
                            $self->assertSame('ch-xyz', $headers['Taler-Challenge-Ids']);
                            $step++;
                            return $responseRepeat;
                        default:
                            $self->fail('Unexpected additional HTTP request in CreateAccount 2FA flow success test');
                    }
                }
            );

        $challengeResponse = $this->bankClient->createAccount($details);

        $this->assertInstanceOf(\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse::class, $challengeResponse);
        $this->assertCount(1, $challengeResponse->challenges);
        $challengeId = $challengeResponse->challenges[0]->challenge_id;
        $this->assertSame('ch-xyz', $challengeId);

        $this->twoFaClient->requestChallenge($instanceId, $challengeId);

        $this->twoFaClient->confirmChallenge(
            $instanceId,
            $challengeId,
            new MerchantChallengeSolveRequest('123456')
        );

        $result = $this->bankClient->createAccount(
            $details,
            ['Taler-Challenge-Ids' => $challengeId]
        );

        $this->assertInstanceOf(AccountAddResponse::class, $result);
        $this->assertSame('hw', $result->h_wire);
        $this->assertSame('s', $result->salt);
        $this->assertSame(4, $step, 'Expected exactly 4 HTTP calls in success 2FA flow');
    }

    public function testCreateAccountTwoFactorFlowFailsWithInvalidTan(): void
    {
        $instanceId = 'inst1';
        $details = new AccountAddDetails('payto://iban/DE123');

        /** @var ResponseInterface&MockObject $responseChallenge */
        $responseChallenge = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamChallenge */
        $streamChallenge = $this->createMock(StreamInterface::class);
        $responseChallenge->method('getStatusCode')->willReturn(202);
        $streamChallenge->method('__toString')->willReturn(json_encode([
            'challenges' => [
                [
                    'challenge_id' => 'ch-fail',
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
                            $self->assertSame('private/accounts', $endpoint);
                            $self->assertSame([], $headers);
                            $self->assertIsString($body);
                            $step++;
                            return $responseChallenge;
                        case 1:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-fail", $endpoint);
                            $self->assertIsString($body);
                            $step++;
                            return $responseRequest;
                        case 2:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-fail/confirm", $endpoint);
                            $self->assertIsString($body);
                            $decoded = json_decode($body, true);
                            $self->assertSame('000000', $decoded['tan'] ?? null);
                            $step++;
                            return $responseConfirmFail;
                        default:
                            $self->fail('Unexpected additional HTTP request in CreateAccount 2FA flow failure test');
                    }
                }
            );

        $challengeResponse = $this->bankClient->createAccount($details);

        $this->assertInstanceOf(\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse::class, $challengeResponse);
        $this->assertCount(1, $challengeResponse->challenges);
        $challengeId = $challengeResponse->challenges[0]->challenge_id;
        $this->assertSame('ch-fail', $challengeId);

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



