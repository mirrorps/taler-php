<?php

namespace Taler\Api\DonauCharity\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\DonauCharity\DonauCharityClient;
use Taler\Api\DonauCharity\Dto\DonauInstancesResponse;
use Taler\Exception\TalerException;

class GetDonauInstances
{
    public function __construct(
        private DonauCharityClient $client
    ) {}

    /**
     * Return all Donau charity instances currently linked to the instance.
     *
     * @param DonauCharityClient $client
     * @param array<string, string> $headers
     * @return DonauInstancesResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-donau
     */
    public static function run(DonauCharityClient $client, array $headers = []): DonauInstancesResponse|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'private/donau', $headers)
            );

            /** @var DonauInstancesResponse|array<string, mixed> $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler get Donau charity instances request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param DonauCharityClient $client
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(DonauCharityClient $client, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', 'private/donau', $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): DonauInstancesResponse
    {
        /**
         * @var array{donau_instances: array<int, array{
         *   donau_instance_serial: int,
         *   donau_url: string,
         *   charity_name: string,
         *   charity_pub_key: string,
         *   charity_id: int,
         *   charity_max_per_year: string,
         *   charity_receipts_to_date: string,
         *   current_year: int,
         *   donau_keys_json?: array<string, mixed>
         * }>} $data
         */
        $data = $this->client->parseResponseBody($response, 200);
        return DonauInstancesResponse::createFromArray($data);
    }
}

 

