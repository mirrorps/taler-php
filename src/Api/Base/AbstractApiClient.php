<?php

namespace Taler\Api\Base;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiClient extends BaseApiClient
{
    /**
     * Handle response wrapping based on configuration
     *
     * @template T
     * @param callable(ResponseInterface): T $handler The response handler function
     * @return T|array<string, mixed>
     */
    public function handleWrappedResponse(callable $handler): mixed
    {
        $response = $this->getResponse();

        if ($this->getTaler()->getConfig()->getWrapResponse()) {
            return $handler($response);
        }

        $decoded = json_decode((string) $response->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }

        return $decoded;
    }
} 