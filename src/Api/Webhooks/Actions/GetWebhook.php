<?php

namespace Taler\Api\Webhooks\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Webhooks\Dto\WebhookDetails;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Exception\TalerException;

class GetWebhook
{
    public function __construct(
        private WebhooksClient $client
    ) {}

    /**
     * Get a single webhook by its ID.
     *
     * @param WebhooksClient $client
     * @param string $webhookId
     * @param array<string, string> $headers
     * @return WebhookDetails|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(WebhooksClient $client, string $webhookId, array $headers = []): WebhookDetails|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', "private/webhooks/{$webhookId}", $headers)
            );

            /** @var WebhookDetails|array<string, mixed> $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler get webhook request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async variant of get webhook.
     *
     * @param WebhooksClient $client
     * @param string $webhookId
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(WebhooksClient $client, string $webhookId, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', "private/webhooks/{$webhookId}", $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): WebhookDetails
    {
        /** @var array{event_type: string, url: string, http_method: string, header_template?: string|null, body_template?: string|null} $data */
        $data = $this->client->parseResponseBody($response, 200);
        return WebhookDetails::createFromArray($data);
    }
}


