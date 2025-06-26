<?php

namespace Taler\Config;

use function Taler\Helpers\isValidUrl;

class TalerConfig
{
    /**
     * TalerConfig constructor.
     * 
     * @param string $baseUrl The base URL for the Taler backend instance
     * @param string $authToken The authentication token for API requests
     * @param bool $wrapResponse Whether to wrap API responses in DTOs
     * @throws \InvalidArgumentException When base URL is empty or invalid
     */
    public function __construct(
        private string $baseUrl,
        private string $authToken = '',
        private bool $wrapResponse = true
    ) {
        $this->validate();
    }

    /**
     * Get the base URL for the Taler backend instance
     * 
     * @return string The configured base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the authentication token for API requests
     * 
     * @return string The configured authentication token
     */
    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    /**
     * Get whether API responses should be wrapped in DTOs
     * 
     * @return bool True if responses should be wrapped, false otherwise
     */
    public function getWrapResponse(): bool
    {
        return $this->wrapResponse;
    }

    /**
     * Validate the configuration
     * 
     * @throws \InvalidArgumentException When base URL is empty or invalid
     */
    private function validate(): void
    {
        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('Missing required "base_url" in options.');
        }

        if (!isValidUrl($this->baseUrl)) {
            throw new \InvalidArgumentException('Invalid base URL provided, only https schema is allowed');
        }
    }

    /**
     * Set a single configuration attribute
     * 
     * @param string $name The name of the attribute to set
     * @param mixed $value The value to set
     * @throws \InvalidArgumentException When the attribute does not exist
     */
    public function setAttribute(string $name, mixed $value): void
    {
        if(property_exists($this, $name) === false){
            throw new \InvalidArgumentException("The attribute '$name' does not exist.");
        }

        $this->$name = $value;
    }

    /**
     * Set multiple configuration attributes at once
     * 
     * @param array<string, mixed> $attributes Array of attribute names and values to set
     * @throws \InvalidArgumentException When any of the attributes do not exist
     */
    public function setAttributes(array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Get a string representation of the configuration
     * 
     * @return string The string representation of the configuration
     */
    public function __toString(): string
    {
        return json_encode([
            'baseUrl' => $this->baseUrl,
            'wrapResponse' => $this->wrapResponse
        ]);
    }

    public function toHash(): string
    {
        return hash('sha256', $this->__toString());
    }
}