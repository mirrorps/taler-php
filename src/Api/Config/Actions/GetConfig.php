<?php

namespace Taler\Api\Config\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Config\ConfigClient;
use Taler\Api\Config\Dto\MerchantVersionResponse;
use Taler\Exception\TalerException;

class GetConfig
{
    public function __construct(
        private ConfigClient $client
    ) {}

    /**
     * @param ConfigClient $client
     * @param array<string, string> $headers
     * @return MerchantVersionResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(ConfigClient $client, array $headers = []): MerchantVersionResponse|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'config', $headers)
            );

            /** @var MerchantVersionResponse|array<string, mixed> $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler get config request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param ConfigClient $client
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(ConfigClient $client, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', 'config', $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): MerchantVersionResponse
    {
        /** @var array{
         *   version: string,
         *   implementation?: string,
         *   currency: string,
         *   currencies: array<string, array{name: string, currency: string, num_fractional_input_digits: int, num_fractional_normal_digits: int, num_fractional_trailing_zero_digits: int, alt_unit_names: array<numeric-string, string>}>,
         *   exchanges: array<int, array{base_url: string, currency: string, master_pub: string}>,
         *   have_self_provisioning: bool,
         *   have_donau: bool,
         *   mandatory_tan_channels?: array<int, string>
         * } $data */
        $data = $this->client->parseResponseBody($response, 200);

        return MerchantVersionResponse::createFromArray($data);
    }
}


