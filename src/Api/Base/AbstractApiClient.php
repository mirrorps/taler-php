<?php

namespace Taler\Api\Base;

use Psr\Http\Message\ResponseInterface;
use Taler\Exception\TalerException;

use const Taler\Http\HTTP_STATUS_CODE_NO_CONTENT;

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

        // For 204 No Content, don't try to JSON-decode an empty body
        if($response->getStatusCode() === HTTP_STATUS_CODE_NO_CONTENT) {
            return null;
        }

        return $this->decodeResponseBody($response);
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
        if ($response->getStatusCode() !== $expectedStatusCode) {
            throw new TalerException(
                message: 'Unexpected response status code: ' . $response->getStatusCode(),
                code: $response->getStatusCode(),
                response: $response
            );
        }

        // For 204 No Content, don't try to JSON-decode an empty body
        if ($expectedStatusCode === HTTP_STATUS_CODE_NO_CONTENT) {
            return null;
        }

        return $this->decodeResponseBody($response);
    }

    /**
     * @param ResponseInterface $response
     * @param bool $associative
     * @param int<1, max> $depth
     */
    public function decodeResponseBody(
        ResponseInterface $response,
        bool $associative = true,
        int $depth = 512,
    ): mixed
    {
        try {
            return json_decode((string)$response->getBody(), $associative, $depth, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new TalerException(
                message: 'Failed to decode response JSON: ' . $e->getMessage(),
                code: $response->getStatusCode(),
                previous: $e,
                response: $response
            );
        }
    }
} 