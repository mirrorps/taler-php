<?php

namespace Taler\Api\Templates\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Templates\Dto\TemplatesSummaryResponse;
use Taler\Api\Templates\TemplatesClient;
use Taler\Exception\TalerException;

class GetTemplates
{
    public function __construct(
        private TemplatesClient $client
    ) {}

    /**
     * Get all templates for the merchant instance.
     *
     * @param TemplatesClient $client
     * @param array<string, string> $headers Optional request headers
     * @return TemplatesSummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-templates
     */
    public static function run(TemplatesClient $client, array $headers = []): TemplatesSummaryResponse|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'private/templates', $headers)
            );

            /** @var TemplatesSummaryResponse|array{templates: array<int, array<string, mixed>>} $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get templates request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant of get templates.
     *
     * @param TemplatesClient $client
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public static function runAsync(TemplatesClient $client, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', 'private/templates', $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): TemplatesSummaryResponse
    {
        /** @var array{templates: array<int, array{template_id: string, template_description: string}>} $data */
        $data = $this->client->parseResponseBody($response, 200);
        return TemplatesSummaryResponse::createFromArray($data);
    }
}


