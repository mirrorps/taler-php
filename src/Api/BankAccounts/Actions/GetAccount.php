<?php

namespace Taler\Api\BankAccounts\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\BankAccountDetail;
use Taler\Exception\TalerException;

class GetAccount
{
    public function __construct(
        private BankAccountClient $client
    ) {}

    /**
     * @param BankAccountClient $client
     * @param string $hWire Hash of the wire details identifying the account
     * @param array<string, string> $headers
     * @return BankAccountDetail|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(BankAccountClient $client, string $hWire, array $headers = []): BankAccountDetail|array
    {
        $action = new self($client);

        try {
            $client->setResponse(
                $client->getClient()->request('GET', "private/accounts/$hWire", $headers)
            );

            /** @var BankAccountDetail|array<string, mixed> $result */
            $result = $client->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get bank account request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param BankAccountClient $client
     * @param string $hWire
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(BankAccountClient $client, string $hWire, array $headers = []): mixed
    {
        $action = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', "private/accounts/$hWire", $headers)
            ->then(function (ResponseInterface $response) use ($action) {
                $action->client->setResponse($response);
                return $action->client->handleWrappedResponse($action->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): BankAccountDetail
    {
        $data = $this->client->parseResponseBody($response, 200);
        return BankAccountDetail::fromArray($data);
    }
}


