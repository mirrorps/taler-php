<?php

namespace Taler\Api\Webhooks\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Webhooks\Dto\WebhookPatchDetails;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Exception\TalerException;

class UpdateWebhook
{
    public function __construct(
        private WebhooksClient $client
    ) {}

    /**
     * Updates a webhook.
     *
     * @param WebhooksClient $client
     * @param string $webhookId
     * @param WebhookPatchDetails $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        WebhooksClient $client,
        string $webhookId,
        WebhookPatchDetails $details,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'PATCH',
                    "private/webhooks/{$webhookId}",
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler update webhook request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Updates a webhook asynchronously.
     *
     * @param WebhooksClient $client
     * @param string $webhookId
     * @param WebhookPatchDetails $details
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        WebhooksClient $client,
        string $webhookId,
        WebhookPatchDetails $details,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('PATCH', "private/webhooks/{$webhookId}", $headers, $body)
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


