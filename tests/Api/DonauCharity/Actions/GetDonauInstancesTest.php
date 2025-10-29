<?php

namespace Taler\Tests\Api\DonauCharity\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\DonauCharity\Actions\GetDonauInstances;
use Taler\Api\DonauCharity\DonauCharityClient;
use Taler\Api\DonauCharity\Dto\DonauInstance;
use Taler\Api\DonauCharity\Dto\DonauInstancesResponse;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetDonauInstancesTest extends TestCase
{
    private DonauCharityClient $client;
    private ResponseInterface&MockObject $response;
    private StreamInterface&MockObject $stream;
    private LoggerInterface&MockObject $logger;
    private Taler&MockObject $taler;
    private HttpClientWrapper&MockObject $httpClientWrapper;

    protected function setUp(): void
    {
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->taler = $this->createMock(Taler::class);

        $this->taler->method('getLogger')->willReturn($this->logger);
        $this->taler->method('getConfig')->willReturn(new TalerConfig('https://example.com', '', true));

        $this->httpClientWrapper = $this->getMockBuilder(HttpClientWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'requestAsync'])
            ->getMock();

        $this->client = new DonauCharityClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $expected = [
            'donau_instances' => [
                [
                    'donau_instance_serial' => 1,
                    'donau_url' => 'https://donau.example',
                    'charity_name' => 'Charity A',
                    'charity_pub_key' => 'PUB_A',
                    'charity_id' => 10,
                    'charity_max_per_year' => 'EUR:1000',
                    'charity_receipts_to_date' => 'EUR:123.45',
                    'current_year' => 2025,
                ],
            ],
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/donau', $headers)
            ->willReturn($this->response);

        $result = GetDonauInstances::run($this->client, $headers);

        $this->assertInstanceOf(DonauInstancesResponse::class, $result);
        $this->assertCount(1, $result->donau_instances);
        $this->assertInstanceOf(DonauInstance::class, $result->donau_instances[0]);
        $this->assertSame('Charity A', $result->donau_instances[0]->charity_name);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetDonauInstances::run($this->client);
    }

    public function testRunAsync(): void
    {
        $expected = [
            'donau_instances' => [
                [
                    'donau_instance_serial' => 2,
                    'donau_url' => 'https://donau.example',
                    'charity_name' => 'Charity B',
                    'charity_pub_key' => 'PUB_B',
                    'charity_id' => 11,
                    'charity_max_per_year' => 'USD:5000',
                    'charity_receipts_to_date' => 'USD:100',
                    'current_year' => 2025,
                ],
            ],
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/donau', [])
            ->willReturn($promise);

        $result = GetDonauInstances::runAsync($this->client);
        $promise->resolve($this->response);

        $this->assertInstanceOf(DonauInstancesResponse::class, $result->wait());
        $this->assertSame('Charity B', $result->wait()->donau_instances[0]->charity_name);
    }
}

 

