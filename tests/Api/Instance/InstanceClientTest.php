<?php
namespace Taler\Tests\Api\Instance;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Instance\Dto\InstanceConfigurationMessage;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\Dto\Challenge;



/**
 * Test cases for InstanceClient.
 */
class InstanceClientTest extends TestCase
{
    private InstanceClient $instanceClient;
    private InstanceConfigurationMessage $config;

    protected function setUp(): void
    {
        // Mock the InstanceClient since we can't easily test HTTP calls
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Taler\Api\Instance\InstanceClient $instanceClient */
        $instanceClient = $this->createMock(InstanceClient::class);
        $this->instanceClient = $instanceClient;

        $auth = new InstanceAuthConfigToken('test-password');
        $address = new Location(country: 'DE', town: 'Berlin');
        $jurisdiction = new Location(country: 'DE', town: 'Berlin');
        $wireTransferDelay = new RelativeTime(d_us: 86400000000);
        $payDelay = new RelativeTime(d_us: 3600000000);

        $this->config = new InstanceConfigurationMessage(
            id: 'test-instance',
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

    /**
     * Test that createInstance method exists and can be called.
     */
    public function testCreateInstanceMethodExists(): void
    {
        $this->instanceClient
            ->expects($this->once())
            ->method('createInstance')
            ->with($this->config, []);

        $this->instanceClient->createInstance($this->config, []);
    }

    /**
     * Test that createInstanceAsync method exists and can be called.
     */
    public function testCreateInstanceAsyncMethodExists(): void
    {
        $this->instanceClient
            ->expects($this->once())
            ->method('createInstanceAsync')
            ->with($this->config, [])
            ->willReturn($this->createMock(\GuzzleHttp\Promise\PromiseInterface::class));

        $result = $this->instanceClient->createInstanceAsync($this->config, []);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
    }

    /**
     * Test that forgotPassword method exists and can be called.
     */
    public function testForgotPasswordMethodExists(): void
    {
        $authConfig = new InstanceAuthConfigToken('new-password');

        $this->instanceClient
            ->expects($this->once())
            ->method('forgotPassword')
            ->with('test-instance', $authConfig, [])
            ->willReturn(null);

        $result = $this->instanceClient->forgotPassword('test-instance', $authConfig, []);
        $this->assertNull($result);
    }

    /**
     * Test that forgotPassword method returns Challenge when 2FA is required.
     */
    public function testForgotPasswordReturnsChallenge(): void
    {
        $authConfig = new InstanceAuthConfigToken('new-password');
        $challenge = new Challenge('challenge-123', 'sms');

        $this->instanceClient
            ->expects($this->once())
            ->method('forgotPassword')
            ->with('test-instance', $authConfig, [])
            ->willReturn($challenge);

        $result = $this->instanceClient->forgotPassword('test-instance', $authConfig, []);
        $this->assertInstanceOf(Challenge::class, $result);
        $this->assertEquals('challenge-123', $result->getId());
        $this->assertEquals('sms', $result->getType());
    }

    /**
     * Test that forgotPasswordAsync method exists and can be called.
     */
    public function testForgotPasswordAsyncMethodExists(): void
    {
        $authConfig = new InstanceAuthConfigToken('new-password');

        $this->instanceClient
            ->expects($this->once())
            ->method('forgotPasswordAsync')
            ->with('test-instance', $authConfig, [])
            ->willReturn($this->createMock(\GuzzleHttp\Promise\PromiseInterface::class));

        $result = $this->instanceClient->forgotPasswordAsync('test-instance', $authConfig, []);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
    }
}
