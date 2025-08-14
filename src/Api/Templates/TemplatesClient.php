<?php
namespace Taler\Api\Templates;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Templates\Dto\TemplateAddDetails;
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
}