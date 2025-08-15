<?php

namespace Taler\Api\Webhooks\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Webhooks\Dto\WebhookSummaryResponse;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Exception\TalerException;

class GetWebhooks
{
    public function __construct(
        private WebhooksClient $client
    ) {}

    /**
     * @param WebhooksClient $client
     * @param array<string, string> $headers
     * @return WebhookSummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(WebhooksClient $client, array $headers = []): WebhookSummaryResponse|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'private/webhooks', $headers)
            );

            /** @var WebhookSummaryResponse|array{webhooks: array<int, array{webhook_id: string, event_type: string}>} $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get webhooks request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param WebhooksClient $client
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(WebhooksClient $client, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', 'private/webhooks', $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): WebhookSummaryResponse
    {
        $data = $this->client->parseResponseBody($response, 200);
        return WebhookSummaryResponse::createFromArray($data);
    }
}


