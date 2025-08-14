<?php
namespace Taler\Api\Templates;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Templates\Dto\TemplateAddDetails;
use Taler\Api\Templates\Dto\TemplatePatchDetails;
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
}