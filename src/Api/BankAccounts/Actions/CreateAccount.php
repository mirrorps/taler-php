<?php

namespace Taler\Api\BankAccounts\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\AccountAddDetails;
use Taler\Api\BankAccounts\Dto\AccountAddResponse;
use Taler\Exception\TalerException;

class CreateAccount
{
    public function __construct(
        private BankAccountClient $client
    ) {}

    /**
     * @param BankAccountClient $client
     * @param AccountAddDetails $details
     * @param array<string, string> $headers
     * @return AccountAddResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        BankAccountClient $client,
        AccountAddDetails $details,
        array $headers = []
    ): AccountAddResponse|array {
        $action = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $action->client->setResponse(
                $action->client->getClient()->request(
                    'POST',
                    'private/accounts',
                    $headers,
                    $body
                )
            );

            /** @var AccountAddResponse|array{h_wire: string, salt: string} $result */
            $result = $client->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler create bank account request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param BankAccountClient $client
     * @param AccountAddDetails $details
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        BankAccountClient $client,
        AccountAddDetails $details,
        array $headers = []
    ): mixed {
        $action = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('POST', 'private/accounts', $headers, $body)
            ->then(function (ResponseInterface $response) use ($action) {
                $action->client->setResponse($response);
                return $action->client->handleWrappedResponse($action->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): AccountAddResponse
    {
        $data = $this->client->parseResponseBody($response, 200);
        return AccountAddResponse::fromArray($data);
    }
}


