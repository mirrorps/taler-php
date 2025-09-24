<?php

namespace Taler\Api\Webhooks\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Exception\TalerException;

class DeleteWebhook
{
    public function __construct(
        private WebhooksClient $client
    ) {}

    /**
     * Delete a single webhook by its ID.
     *
     * @param WebhooksClient $client
     * @param string $webhookId
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        WebhooksClient $client,
        string $webhookId,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request(
                    'DELETE',
                    "private/webhooks/{$webhookId}",
                    $headers
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler delete webhook request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param WebhooksClient $client
     * @param string $webhookId
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(
        WebhooksClient $client,
        string $webhookId,
        array $headers = []
    ): mixed {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('DELETE', "private/webhooks/{$webhookId}", $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        $this->client->parseResponseBody($response, 204);
    }
}


