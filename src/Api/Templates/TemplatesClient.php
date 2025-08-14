<?php
namespace Taler\Api\Templates;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Templates\Dto\TemplateAddDetails;
use Taler\Api\Templates\Dto\TemplatePatchDetails;
use Taler\Api\Templates\Dto\TemplatesSummaryResponse;
use Taler\Api\Templates\Dto\TemplateDetails;
use Taler\Exception\TalerException;

class TemplatesClient extends AbstractApiClient
{
    /**
     * @param TemplateAddDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function createTemplate(TemplateAddDetails $details, array $headers = []): void
    {
        Actions\CreateTemplate::run($this, $details, $headers);
    }

    /**
     * @param TemplateAddDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createTemplateAsync(TemplateAddDetails $details, array $headers = []): mixed
    {
        return Actions\CreateTemplate::runAsync($this, $details, $headers);
    }

    /**
     * @param string $templateId
     * @param TemplatePatchDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateTemplate(string $templateId, TemplatePatchDetails $details, array $headers = []): void
    {
        Actions\UpdateTemplate::run($this, $templateId, $details, $headers);
    }

    /**
     * @param string $templateId
     * @param TemplatePatchDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateTemplateAsync(string $templateId, TemplatePatchDetails $details, array $headers = []): mixed
    {
        return Actions\UpdateTemplate::runAsync($this, $templateId, $details, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return TemplatesSummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getTemplates(array $headers = []): TemplatesSummaryResponse|array
    {
        return Actions\GetTemplates::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getTemplatesAsync(array $headers = []): mixed
    {
        return Actions\GetTemplates::runAsync($this, $headers);
    }

    /**
     * @param string $templateId
     * @param array<string, string> $headers Optional request headers
     * @return TemplateDetails|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getTemplate(string $templateId, array $headers = []): TemplateDetails|array
    {
        return Actions\GetTemplate::run($this, $templateId, $headers);
    }

    /**
     * @param string $templateId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getTemplateAsync(string $templateId, array $headers = []): mixed
    {
        return Actions\GetTemplate::runAsync($this, $templateId, $headers);
    }
}