<?php

namespace Taler\Api\BankAccounts\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\AccountPatchDetails;
use Taler\Exception\TalerException;

class UpdateAccount
{
    public function __construct(
        private BankAccountClient $client
    ) {}

    /**
     * Update an existing bank account by its h_wire.
     *
     * @param BankAccountClient $client
     * @param string $hWire
     * @param AccountPatchDetails $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        BankAccountClient $client,
        string $hWire,
        AccountPatchDetails $details,
        array $headers = []
    ): void {
        $action = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $action->client->setResponse(
                $action->client->getClient()->request(
                    'PATCH',
                    "private/accounts/{$hWire}",
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($action->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler update bank account request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param BankAccountClient $client
     * @param string $hWire
     * @param AccountPatchDetails $details
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(
        BankAccountClient $client,
        string $hWire,
        AccountPatchDetails $details,
        array $headers = []
    ): mixed {
        $action = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('PATCH', "private/accounts/{$hWire}", $headers, $body)
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



