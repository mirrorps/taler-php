<?php

namespace Taler\Api\WireTransfers\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\WireTransfers\WireTransfersClient;
use Taler\Exception\TalerException;

class DeleteTransfer
{
    public function __construct(
        private WireTransfersClient $client
    ) {}

    /**
     * Delete a wire transfer by its transfer serial ID ($TID).
     *
     * @param WireTransfersClient $client
     * @param string $tid
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#delete-[-instances-$INSTANCE]-private-transfers-$TID
     */
    public static function run(
        WireTransfersClient $client,
        string $tid,
        array $headers = []
    ): void {
        $action = new self($client);

        try {
            $action->client->setResponse(
                $action->client->getClient()->request(
                    'DELETE',
                    "private/transfers/{$tid}",
                    $headers
                )
            );

            $client->handleWrappedResponse($action->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler delete transfer request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param WireTransfersClient $client
     * @param string $tid
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(
        WireTransfersClient $client,
        string $tid,
        array $headers = []
    ): mixed {
        $action = new self($client);

        return $client
            ->getClient()
            ->requestAsync('DELETE', "private/transfers/{$tid}", $headers)
            ->then(function (ResponseInterface $response) use ($action) {
                $action->client->setResponse($response);
                return $action->client->handleWrappedResponse($action->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        $this->client->parseResponseBody($response, 204);
    }
}


