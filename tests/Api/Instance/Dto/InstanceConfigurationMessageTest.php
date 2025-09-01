<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\Dto\InstanceAuthConfigTokenOLD;
use Taler\Api\Instance\Dto\InstanceAuthConfigExternal;
use Taler\Api\Instance\Dto\InstanceConfigurationMessage;

/**
 * Test cases for InstanceConfigurationMessage DTO.
 */
class InstanceConfigurationMessageTest extends TestCase
{
    private InstanceAuthConfigToken $authToken;
    private Location $address;
    private Location $jurisdiction;
    private RelativeTime $wireTransferDelay;
    private RelativeTime $payDelay;

    protected function setUp(): void
    {
        $this->authToken = new InstanceAuthConfigToken('test-password');

        $this->address = new Location(
            country: 'DE',
            town: 'Berlin'
        );

        $this->jurisdiction = new Location(
            country: 'DE',
            town: 'Berlin'
        );

        $this->wireTransferDelay = new RelativeTime(d_us: 86400000000); // 1 day
        $this->payDelay = new RelativeTime(d_us: 3600000000); // 1 hour
    }

    /**
     * Test valid construction with Token auth.
     */
    public function testValidConstructionWithTokenAuth(): void
    {
        $config = new InstanceConfigurationMessage(
            id: 'test-instance',
            name: 'Test Instance',
            email: 'test@example.com',
            phone_number: '+49123456789',
            website: 'https://example.com',
            logo: 'https://example.com/logo.png',
            auth: $this->authToken,
            address: $this->address,
            jurisdiction: $this->jurisdiction,
            use_stefan: true,
            default_wire_transfer_delay: $this->wireTransferDelay,
            default_pay_delay: $this->payDelay
        );

        $this->assertSame('test-instance', $config->id);
        $this->assertSame('Test Instance', $config->name);
        $this->assertSame('test@example.com', $config->email);
        $this->assertSame('+49123456789', $config->phone_number);
        $this->assertSame('https://example.com', $config->website);
        $this->assertSame('https://example.com/logo.png', $config->logo);
        $this->assertSame($this->authToken, $config->auth);
        $this->assertSame($this->address, $config->address);
        $this->assertSame($this->jurisdiction, $config->jurisdiction);
        $this->assertTrue($config->use_stefan);
        $this->assertSame($this->wireTransferDelay, $config->default_wire_transfer_delay);
        $this->assertSame($this->payDelay, $config->default_pay_delay);
    }

    /**
     * Test valid construction with minimal required fields.
     */
    public function testValidConstructionMinimal(): void
    {
        $config = new InstanceConfigurationMessage(
            id: 'test-instance',
            name: 'Test Instance',
            email: null,
            phone_number: null,
            website: null,
            logo: null,
            auth: $this->authToken,
            address: $this->address,
            jurisdiction: $this->jurisdiction,
            use_stefan: false,
            default_wire_transfer_delay: $this->wireTransferDelay,
            default_pay_delay: $this->payDelay
        );

        $this->assertSame('test-instance', $config->id);
        $this->assertSame('Test Instance', $config->name);
        $this->assertNull($config->email);
        $this->assertNull($config->phone_number);
        $this->assertNull($config->website);
        $this->assertNull($config->logo);
        $this->assertSame($this->authToken, $config->auth);
        $this->assertSame($this->address, $config->address);
        $this->assertSame($this->jurisdiction, $config->jurisdiction);
        $this->assertFalse($config->use_stefan);
        $this->assertSame($this->wireTransferDelay, $config->default_wire_transfer_delay);
        $this->assertSame($this->payDelay, $config->default_pay_delay);
    }

    /**
     * Test validation with empty ID.
     */
    public function testValidationEmptyId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Instance ID cannot be empty');

        new InstanceConfigurationMessage(
            id: '',
            name: 'Test Instance',
            email: null,
            phone_number: null,
            website: null,
            logo: null,
            auth: $this->authToken,
            address: $this->address,
            jurisdiction: $this->jurisdiction,
            use_stefan: false,
            default_wire_transfer_delay: $this->wireTransferDelay,
            default_pay_delay: $this->payDelay
        );
    }

    /**
     * Test validation with invalid ID format.
     */
    public function testValidationInvalidIdFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Instance ID must match regex ^[A-Za-z0-9][A-Za-z0-9_.@-]+$');

        new InstanceConfigurationMessage(
            id: 'invalid@id!',
            name: 'Test Instance',
            email: null,
            phone_number: null,
            website: null,
            logo: null,
            auth: $this->authToken,
            address: $this->address,
            jurisdiction: $this->jurisdiction,
            use_stefan: false,
            default_wire_transfer_delay: $this->wireTransferDelay,
            default_pay_delay: $this->payDelay
        );
    }

    /**
     * Test validation with empty name.
     */
    public function testValidationEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Instance name cannot be empty');

        new InstanceConfigurationMessage(
            id: 'test-instance',
            name: '',
            email: null,
            phone_number: null,
            website: null,
            logo: null,
            auth: $this->authToken,
            address: $this->address,
            jurisdiction: $this->jurisdiction,
            use_stefan: false,
            default_wire_transfer_delay: $this->wireTransferDelay,
            default_pay_delay: $this->payDelay
        );
    }

    /**
     * Test validation with invalid email.
     */
    public function testValidationInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        new InstanceConfigurationMessage(
            id: 'test-instance',
            name: 'Test Instance',
            email: 'invalid-email',
            phone_number: null,
            website: null,
            logo: null,
            auth: $this->authToken,
            address: $this->address,
            jurisdiction: $this->jurisdiction,
            use_stefan: false,
            default_wire_transfer_delay: $this->wireTransferDelay,
            default_pay_delay: $this->payDelay
        );
    }

    /**
     * Test createFromArray with Token auth.
     */
    public function testCreateFromArrayWithTokenAuth(): void
    {
        $data = [
            'id' => 'test-instance',
            'name' => 'Test Instance',
            'email' => 'test@example.com',
            'phone_number' => '+49123456789',
            'website' => 'https://example.com',
            'logo' => 'https://example.com/logo.png',
            'auth' => [
                'method' => 'token',
                'password' => 'test-password'
            ],
            'address' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'jurisdiction' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'use_stefan' => true,
            'default_wire_transfer_delay' => ['d_us' => 86400000000],
            'default_pay_delay' => ['d_us' => 3600000000]
        ];

        $config = InstanceConfigurationMessage::createFromArray($data);

        $this->assertInstanceOf(InstanceConfigurationMessage::class, $config);
        $this->assertSame('test-instance', $config->id);
        $this->assertSame('Test Instance', $config->name);
        $this->assertSame('test@example.com', $config->email);
        $this->assertSame('+49123456789', $config->phone_number);
        $this->assertSame('https://example.com', $config->website);
        $this->assertSame('https://example.com/logo.png', $config->logo);
        $this->assertInstanceOf(InstanceAuthConfigToken::class, $config->auth);
        $this->assertSame('test-password', $config->auth->password);
        $this->assertInstanceOf(Location::class, $config->address);
        $this->assertInstanceOf(Location::class, $config->jurisdiction);
        $this->assertTrue($config->use_stefan);
        $this->assertInstanceOf(RelativeTime::class, $config->default_wire_transfer_delay);
        $this->assertInstanceOf(RelativeTime::class, $config->default_pay_delay);
    }

    /**
     * Test createFromArray with TokenOLD auth.
     */
    public function testCreateFromArrayWithTokenOldAuth(): void
    {
        $data = [
            'id' => 'test-instance',
            'name' => 'Test Instance',
            'auth' => [
                'method' => 'token',
                'token' => 'secret-token:test-token'
            ],
            'address' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'jurisdiction' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'use_stefan' => false,
            'default_wire_transfer_delay' => ['d_us' => 86400000000],
            'default_pay_delay' => ['d_us' => 3600000000]
        ];

        $config = InstanceConfigurationMessage::createFromArray($data);

        $this->assertInstanceOf(InstanceConfigurationMessage::class, $config);
        $this->assertInstanceOf(InstanceAuthConfigTokenOLD::class, $config->auth);
        $this->assertSame('secret-token:test-token', $config->auth->token);
    }

    /**
     * Test createFromArray with External auth.
     */
    public function testCreateFromArrayWithExternalAuth(): void
    {
        $data = [
            'id' => 'test-instance',
            'name' => 'Test Instance',
            'auth' => [
                'method' => 'external'
            ],
            'address' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'jurisdiction' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'use_stefan' => false,
            'default_wire_transfer_delay' => ['d_us' => 86400000000],
            'default_pay_delay' => ['d_us' => 3600000000]
        ];

        $config = InstanceConfigurationMessage::createFromArray($data);

        $this->assertInstanceOf(InstanceConfigurationMessage::class, $config);
        $this->assertInstanceOf(InstanceAuthConfigExternal::class, $config->auth);
    }

    /**
     * Test createFromArray with invalid auth method.
     */
    public function testCreateFromArrayInvalidAuthMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid auth method');

        $data = [
            'id' => 'test-instance',
            'name' => 'Test Instance',
            'auth' => [
                'method' => 'invalid'
            ],
            'address' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'jurisdiction' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'use_stefan' => false,
            'default_wire_transfer_delay' => ['d_us' => 86400000000],
            'default_pay_delay' => ['d_us' => 3600000000]
        ];

        InstanceConfigurationMessage::createFromArray($data);
    }

    /**
     * Test skipping validation.
     */
    public function testSkipValidation(): void
    {
        $config = new InstanceConfigurationMessage(
            id: '', // Would normally fail validation
            name: 'Test Instance',
            email: null,
            phone_number: null,
            website: null,
            logo: null,
            auth: $this->authToken,
            address: $this->address,
            jurisdiction: $this->jurisdiction,
            use_stefan: false,
            default_wire_transfer_delay: $this->wireTransferDelay,
            default_pay_delay: $this->payDelay,
            validate: false
        );

        $this->assertSame('', $config->id);
    }
}
