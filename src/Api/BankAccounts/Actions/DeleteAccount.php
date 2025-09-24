<?php

namespace Taler\Api\BankAccounts\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Exception\TalerException;

class DeleteAccount
{
    public function __construct(
        private BankAccountClient $client
    ) {}

    /**
     * Delete a bank account by its h_wire.
     *
     * @param BankAccountClient $client
     * @param string $hWire
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        BankAccountClient $client,
        string $hWire,
        array $headers = []
    ): void {
        $action = new self($client);

        try {
            $action->client->setResponse(
                $action->client->getClient()->request(
                    'DELETE',
                    "private/accounts/{$hWire}",
                    $headers
                )
            );

            $client->handleWrappedResponse($action->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler delete bank account request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param BankAccountClient $client
     * @param string $hWire
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(
        BankAccountClient $client,
        string $hWire,
        array $headers = []
    ): mixed {
        $action = new self($client);

        return $client
            ->getClient()
            ->requestAsync('DELETE', "private/accounts/{$hWire}", $headers)
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


