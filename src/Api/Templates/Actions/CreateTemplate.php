<?php

namespace Taler\Api\Templates\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Templates\Dto\TemplateAddDetails;
use Taler\Api\Templates\TemplatesClient;
use Taler\Exception\TalerException;

class CreateTemplate
{
    public function __construct(
        private TemplatesClient $client
    ) {}

    /**
     * Creates a new template.
     *
     * @param TemplatesClient $client
     * @param TemplateAddDetails $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#post-[-instances-$INSTANCE]-private-templates
     */
    public static function run(
        TemplatesClient $client,
        TemplateAddDetails $details,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'POST',
                    'private/templates',
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler create template request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Creates a new template asynchronously.
     *
     * @param TemplatesClient $client
     * @param TemplateAddDetails $details
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        TemplatesClient $client,
        TemplateAddDetails $details,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('POST', 'private/templates', $headers, $body)
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


