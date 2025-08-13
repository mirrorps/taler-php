<?php

namespace Taler\Api\WireTransfers\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\WireTransfers\Dto\GetTransfersRequest;
use Taler\Api\WireTransfers\Dto\TransfersList;
use Taler\Api\WireTransfers\WireTransfersClient;
use Taler\Exception\TalerException;

class GetTransfers
{
    public function __construct(
        private WireTransfersClient $client
    ) {}

    /**
     * @param GetTransfersRequest|null $request Request params
     * @param array<string, string> $headers HTTP headers
     * @return TransfersList|array{transfers: array<int, array<string, mixed>>}
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-transfers
     */
    public static function run(
        WireTransfersClient $client,
        ?GetTransfersRequest $request = null,
        array $headers = []
    ): TransfersList|array {
        $self = new self($client);

        try {
            $query = $request ? http_build_query($request->toArray()) : '';
            $path = 'private/transfers' . ($query !== '' ? ('?' . $query) : '');

            $self->client->setResponse(
                $self->client->getClient()->request('GET', $path, $headers)
            );

            /** @var TransfersList|array{transfers: array<int, array<string, mixed>>} $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get transfers request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    private function handleResponse(ResponseInterface $response): TransfersList
    {
        /** @var array{transfers: array<int, array<string, mixed>>} $data */
        $data = $this->client->parseResponseBody($response, 200);
        return TransfersList::createFromArray($data);
    }

    /**
     * @param GetTransfersRequest|null $request Request params
     * @param array<string, string> $headers HTTP headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        WireTransfersClient $client,
        ?GetTransfersRequest $request = null,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $query = $request ? http_build_query($request->toArray()) : '';
        $path = 'private/transfers' . ($query !== '' ? ('?' . $query) : '');

        return $client
            ->getClient()
            ->requestAsync('GET', $path, $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }
}


