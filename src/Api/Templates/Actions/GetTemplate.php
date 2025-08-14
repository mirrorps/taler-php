<?php

namespace Taler\Api\Templates\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Templates\Dto\TemplateDetails;
use Taler\Api\Templates\TemplatesClient;
use Taler\Exception\TalerException;

class GetTemplate
{
    public function __construct(
        private TemplatesClient $client
    ) {}

    /**
     * Get a single template by its ID.
     *
     * @param TemplatesClient $client
     * @param string $templateId
     * @param array<string, string> $headers
     * @return TemplateDetails|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-templates-$TEMPLATE_ID
     */
    public static function run(TemplatesClient $client, string $templateId, array $headers = []): TemplateDetails|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', "private/templates/{$templateId}", $headers)
            );

            /** @var TemplateDetails|array<string, mixed> $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get template request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant of get template.
     *
     * @param TemplatesClient $client
     * @param string $templateId
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(TemplatesClient $client, string $templateId, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', "private/templates/{$templateId}", $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): TemplateDetails
    {
        /** @var array{
        *   template_id: string,
        *   template_description: string,
        *   template_contract: array{summary?: string, currency?: string, amount?: string, minimum_age: int, pay_duration: array{d_us: int|string}},
        *   otp_id?: string|null,
        *   editable_defaults?: array<string, mixed>|null
        * } $data */
        $data = $this->client->parseResponseBody($response, 200);
        return TemplateDetails::createFromArray($data);
    }
}


