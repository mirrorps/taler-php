<?php

namespace Taler\Api\BankAccounts\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\AccountsSummaryResponse;
use Taler\Exception\TalerException;

class GetAccounts
{
    public function __construct(
        private BankAccountClient $client
    ) {}

    /**
     * @param BankAccountClient $client
     * @param array<string, string> $headers
     * @return AccountsSummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(BankAccountClient $client, array $headers = []): AccountsSummaryResponse|array
    {
        $action = new self($client);

        try {
            $action->client->setResponse(
                $action->client->getClient()->request('GET', 'private/accounts', $headers)
            );

            /** @var AccountsSummaryResponse|array{accounts: array<int, array{payto_uri: string, h_wire: string, active: bool}>} $result */
            $result = $client->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler get bank accounts request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param BankAccountClient $client
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(BankAccountClient $client, array $headers = []): mixed
    {
        $action = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', 'private/accounts', $headers)
            ->then(function (ResponseInterface $response) use ($action) {
                $action->client->setResponse($response);
                return $action->client->handleWrappedResponse($action->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): AccountsSummaryResponse
    {
        $data = $this->client->parseResponseBody($response, 200);
        return AccountsSummaryResponse::createFromArray($data);
    }
}


