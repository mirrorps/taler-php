<?php

namespace Taler\Api\Webhooks\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Webhooks\Dto\WebhookAddDetails;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Exception\TalerException;

class CreateWebhook
{
    public function __construct(
        private WebhooksClient $client
    ) {}

    /**
     * Creates a new webhook.
     *
     * @param WebhooksClient $client
     * @param WebhookAddDetails $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        WebhooksClient $client,
        WebhookAddDetails $details,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'POST',
                    'private/webhooks',
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler create webhook request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Creates a new webhook asynchronously.
     *
     * @param WebhooksClient $client
     * @param WebhookAddDetails $details
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        WebhooksClient $client,
        WebhookAddDetails $details,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('POST', 'private/webhooks', $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        // Endpoint returns 204 No Content; parse will check status and return null
        $this->client->parseResponseBody($response, 204);
    }
}


