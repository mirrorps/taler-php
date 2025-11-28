<?php

namespace Taler\Tests\Api\Order\Actions;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Api\Order\Actions\GetOrder;
use Taler\Api\Order\OrderClient;
use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;
use Taler\Api\TwoFactorAuth\TwoFactorAuthClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;
use Taler\Exception\TalerException;

final class GetOrderTwoFactorAuthFlowTest extends TestCase
{
    private HttpClientWrapper&MockObject $httpClient;
    private Taler $taler;
    private OrderClient $orderClient;
    private TwoFactorAuthClient $twoFaClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientWrapper::class);
        $this->taler = new Taler(new TalerConfig('https://example.com', '', true));

        $this->orderClient = new OrderClient($this->taler, $this->httpClient);
        $this->twoFaClient = new TwoFactorAuthClient($this->taler, $this->httpClient);
    }

    public function testGetOrderTwoFactorFlowSuccess(): void
    {
        $instanceId = 'inst1';
        $orderId = 'order-2fa-success';

        /** @var ResponseInterface&MockObject $responseChallenge */
        $responseChallenge = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamChallenge */
        $streamChallenge = $this->createMock(StreamInterface::class);
        $responseChallenge->method('getStatusCode')->willReturn(202);
        $streamChallenge->method('__toString')->willReturn(json_encode([
            'challenges' => [
                [
                    'challenge_id' => 'ch-order-success',
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
            'order_status' => 'paid',
            'refunded' => false,
            'refund_pending' => false,
            'wired' => true,
            'deposit_total' => '100.00',
            'exchange_code' => 200,
            'exchange_http_status' => 200,
            'refund_amount' => '0.00',
            'contract_terms' => [
                'version' => 0,
                'summary' => 'Test Order',
                'order_id' => $orderId,
                'products' => [],
                'timestamp' => ['t_s' => 1234567890],
                'refund_deadline' => ['t_s' => 1234567890],
                'pay_deadline' => ['t_s' => 1234567890],
                'wire_transfer_deadline' => ['t_s' => 1234567890],
                'merchant_pub' => 'merchant_pub_key',
                'merchant_base_url' => 'https://merchant.example.com/',
                'merchant' => [
                    'name' => 'Test Merchant'
                ],
                'h_wire' => 'wire_hash',
                'wire_method' => 'test',
                'exchanges' => [],
                'nonce' => 'test_nonce'
            ],
            'last_payment' => ['t_s' => 1234567890],
            'wire_details' => [],
            'wire_reports' => [],
            'refund_details' => [],
            'order_status_url' => 'https://example.com/status'
        ]));
        $responseRepeat->method('getBody')->willReturn($streamRepeat);

        $step = 0;
        $capturedEndpoint = null;
        $capturedHeaders = null;
        $self = $this;

        $this->httpClient
            ->method('request')
            ->willReturnCallback(
                function (string $method, string $endpoint, array $headers = [], ?string $body = null) use (
                    &$step,
                    &$capturedEndpoint,
                    &$capturedHeaders,
                    $self,
                    $instanceId,
                    $orderId,
                    $responseChallenge,
                    $responseRequest,
                    $responseConfirm,
                    $responseRepeat
                ) {
                    switch ($step) {
                        case 0:
                            $self->assertSame('GET', $method);
                            $self->assertSame("private/orders/{$orderId}?", $endpoint);
                            $self->assertSame(
                                ['X-Taler-Instance' => 'inst1'],
                                $headers
                            );
                            $self->assertNull($body);
                            $capturedEndpoint = $endpoint;
                            $capturedHeaders = $headers;
                            $step++;
                            return $responseChallenge;
                        case 1:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-order-success", $endpoint);
                            $self->assertIsString($body);
                            $step++;
                            return $responseRequest;
                        case 2:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-order-success/confirm", $endpoint);
                            $self->assertIsString($body);
                            $decoded = json_decode($body, true);
                            $self->assertSame('123456', $decoded['tan'] ?? null);
                            $step++;
                            return $responseConfirm;
                        case 3:
                            $self->assertSame('GET', $method);
                            $self->assertSame($capturedEndpoint, $endpoint, 'Repeated endpoint must match original exactly');
                            $expectedHeaders = $capturedHeaders;
                            $expectedHeaders['Taler-Challenge-Ids'] = 'ch-order-success';
                            ksort($expectedHeaders);
                            $sortedActual = $headers;
                            ksort($sortedActual);
                            $self->assertSame($expectedHeaders, $sortedActual);
                            $self->assertNull($body);
                            $step++;
                            return $responseRepeat;
                        default:
                            $self->fail('Unexpected additional HTTP request in GetOrder 2FA flow success test');
                    }
                }
            );

        // 1) Protected GET /instances/$INSTANCE/private/orders/$ORDER_ID -> ChallengeResponse
        $challengeResponse = GetOrder::run(
            $this->orderClient,
            $orderId,
            [],
            [] + ['X-Taler-Instance' => $instanceId]
        );

        $this->assertInstanceOf(\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse::class, $challengeResponse);
        $this->assertCount(1, $challengeResponse->challenges);
        $challengeId = $challengeResponse->challenges[0]->challenge_id;
        $this->assertSame('ch-order-success', $challengeId);

        // 2) Request TAN
        $this->twoFaClient->requestChallenge($instanceId, $challengeId);

        // 3) Confirm TAN
        $this->twoFaClient->confirmChallenge(
            $instanceId,
            $challengeId,
            new MerchantChallengeSolveRequest('123456')
        );

        // 4) Repeat protected GET with Taler-Challenge-Ids header
        $result = GetOrder::run(
            $this->orderClient,
            $orderId,
            [],
            ['Taler-Challenge-Ids' => $challengeId] + ['X-Taler-Instance' => $instanceId]
        );

        $this->assertInstanceOf(CheckPaymentPaidResponse::class, $result);
        $this->assertSame('paid', $result->order_status);
        $this->assertSame('100.00', $result->deposit_total);
        $this->assertSame('Test Order', $result->contract_terms->summary);
        $this->assertSame(4, $step, 'Expected exactly 4 HTTP calls in success 2FA flow');
    }

    public function testGetOrderTwoFactorFlowFailsWithInvalidTan(): void
    {
        $instanceId = 'inst1';
        $orderId = 'order-2fa-fail';

        /** @var ResponseInterface&MockObject $responseChallenge */
        $responseChallenge = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $streamChallenge */
        $streamChallenge = $this->createMock(StreamInterface::class);
        $responseChallenge->method('getStatusCode')->willReturn(202);
        $streamChallenge->method('__toString')->willReturn(json_encode([
            'challenges' => [
                [
                    'challenge_id' => 'ch-order-fail',
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
                    $orderId,
                    $responseChallenge,
                    $responseRequest,
                    $responseConfirmFail
                ) {
                    switch ($step) {
                        case 0:
                            $self->assertSame('GET', $method);
                            $self->assertSame("private/orders/{$orderId}?", $endpoint);
                            $self->assertSame(
                                ['X-Taler-Instance' => 'inst1'],
                                $headers
                            );
                            $self->assertNull($body);
                            $step++;
                            return $responseChallenge;
                        case 1:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-order-fail", $endpoint);
                            $self->assertIsString($body);
                            $step++;
                            return $responseRequest;
                        case 2:
                            $self->assertSame('POST', $method);
                            $self->assertSame("instances/{$instanceId}/challenge/ch-order-fail/confirm", $endpoint);
                            $self->assertIsString($body);
                            $decoded = json_decode($body, true);
                            $self->assertSame('000000', $decoded['tan'] ?? null);
                            $step++;
                            return $responseConfirmFail;
                        default:
                            $self->fail('Unexpected additional HTTP request in GetOrder 2FA flow failure test');
                    }
                }
            );

        $challengeResponse = GetOrder::run(
            $this->orderClient,
            $orderId,
            [],
            [] + ['X-Taler-Instance' => $instanceId]
        );

        $this->assertInstanceOf(\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse::class, $challengeResponse);
        $this->assertCount(1, $challengeResponse->challenges);
        $challengeId = $challengeResponse->challenges[0]->challenge_id;
        $this->assertSame('ch-order-fail', $challengeId);

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



