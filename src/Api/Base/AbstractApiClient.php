<?php

namespace Taler\Api\Base;

use Psr\Http\Message\ResponseInterface;
use Taler\Exception\TalerException;

abstract class AbstractApiClient extends BaseApiClient
{
    /**
     * Handle response wrapping based on configuration
     *
     * @param callable(ResponseInterface): mixed $handler The response handler function
     * @return mixed
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

    /**
     * Parse response body and check status code
     *
     * @param ResponseInterface $response
     * @param int $expectedStatusCode
     * @throws TalerException
     */
    public function parseResponseBody(ResponseInterface $response, int $expectedStatusCode = 200): mixed
    {
        $data = json_decode((string)$response->getBody(), true);

        if ($response->getStatusCode() !== $expectedStatusCode) {
            throw new TalerException('Unexpected response status code: ' . $response->getStatusCode());
        }

        return $data;
    }
} 