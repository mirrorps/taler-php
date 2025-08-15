<?php

namespace Taler\Api\Webhooks;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Webhooks\Dto\WebhookAddDetails;
use Taler\Api\Webhooks\Dto\WebhookPatchDetails;
use Taler\Api\Webhooks\Dto\WebhookSummaryResponse;
use Taler\Api\Webhooks\Dto\WebhookDetails;
use Taler\Exception\TalerException;

class WebhooksClient extends AbstractApiClient
{
    /**
     * @param WebhookAddDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function createWebhook(WebhookAddDetails $details, array $headers = []): void
    {
        Actions\CreateWebhook::run($this, $details, $headers);
    }

    /**
     * @param WebhookAddDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createWebhookAsync(WebhookAddDetails $details, array $headers = []): mixed
    {
        return Actions\CreateWebhook::runAsync($this, $details, $headers);
    }

    /**
     * @param string $webhookId
     * @param WebhookPatchDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateWebhook(string $webhookId, WebhookPatchDetails $details, array $headers = []): void
    {
        Actions\UpdateWebhook::run($this, $webhookId, $details, $headers);
    }

    /**
     * @param string $webhookId
     * @param WebhookPatchDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateWebhookAsync(string $webhookId, WebhookPatchDetails $details, array $headers = []): mixed
    {
        return Actions\UpdateWebhook::runAsync($this, $webhookId, $details, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return WebhookSummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getWebhooks(array $headers = []): WebhookSummaryResponse|array
    {
        return Actions\GetWebhooks::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public function getWebhooksAsync(array $headers = []): mixed
    {
        return Actions\GetWebhooks::runAsync($this, $headers);
    }

    /**
     * @param string $webhookId
     * @param array<string, string> $headers Optional request headers
     * @return WebhookDetails|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getWebhook(string $webhookId, array $headers = []): WebhookDetails|array
    {
        return Actions\GetWebhook::run($this, $webhookId, $headers);
    }

    /**
     * @param string $webhookId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public function getWebhookAsync(string $webhookId, array $headers = []): mixed
    {
        return Actions\GetWebhook::runAsync($this, $webhookId, $headers);
    }
}


