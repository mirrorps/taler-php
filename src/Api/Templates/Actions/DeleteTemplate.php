<?php

namespace Taler\Api\Templates\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Templates\TemplatesClient;
use Taler\Exception\TalerException;

class DeleteTemplate
{
    public function __construct(
        private TemplatesClient $client
    ) {}

    /**
     * Delete a single template by its ID.
     *
     * @param TemplatesClient $client
     * @param string $templateId
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#delete-[-instances-$INSTANCE]-private-templates-$TEMPLATE_ID
     */
    public static function run(
        TemplatesClient $client,
        string $templateId,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request(
                    'DELETE',
                    "private/templates/{$templateId}",
                    $headers
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler delete template request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param TemplatesClient $client
     * @param string $templateId
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(
        TemplatesClient $client,
        string $templateId,
        array $headers = []
    ): mixed {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('DELETE', "private/templates/{$templateId}", $headers)
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



