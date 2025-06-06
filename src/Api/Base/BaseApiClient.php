<?php

namespace Taler\Api\Base;

use Psr\Http\Message\ResponseInterface;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class BaseApiClient
{
    private ResponseInterface $response;

    public function __construct(
        protected Taler $taler,
        protected HttpClientWrapper $client
    ) {
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setClient(HttpClientWrapper $client): void
    {
        $this->client = $client;
    }

    public function getClient(): HttpClientWrapper
    {
        return $this->client;
    }

    public function getTaler(): Taler
    {
        return $this->taler;
    }
}