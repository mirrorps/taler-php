<?php

namespace Taler\Api\Templates\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Templates\Dto\TemplatePatchDetails;
use Taler\Api\Templates\TemplatesClient;
use Taler\Exception\TalerException;

class UpdateTemplate
{
    public function __construct(
        private TemplatesClient $client
    ) {}

    /**
     * Updates a template.
     *
     * @param TemplatesClient $client
     * @param string $templateId
     * @param TemplatePatchDetails $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#patch-[-instances-$INSTANCE]-private-templates-$TEMPLATE_ID
     */
    public static function run(
        TemplatesClient $client,
        string $templateId,
        TemplatePatchDetails $details,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'PATCH',
                    "private/templates/{$templateId}",
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler update template request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param TemplatesClient $client
     * @param string $templateId
     * @param TemplatePatchDetails $details
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(
        TemplatesClient $client,
        string $templateId,
        TemplatePatchDetails $details,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('PATCH', "private/templates/{$templateId}", $headers, $body)
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



