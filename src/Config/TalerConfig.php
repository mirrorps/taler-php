<?php

namespace Taler\Config;

use function Taler\Helpers\isValidBaseUrl;

class TalerConfig
{
    public function __construct(
        private string $baseUrl,
        private string $authToken = ''
    ) {
        $this->validate();
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    private function validate(): void
    {
        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('Missing required "base_url" in options.');
        }

        if (!isValidBaseUrl($this->baseUrl)) {
            throw new \InvalidArgumentException('Invalid base URL provided, only https schema is allowed');
        }
    }
}