<?php

namespace Taler\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var mixed
     */
    public $data;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->data = $this->loadDataFromResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    private function loadDataFromResponse(ResponseInterface $response): mixed
    {
        $contents = $response->getBody()->getContents();

        return $contents ? json_decode($contents) : null;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * @param string $version
     * @return MessageInterface
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        return $this->response->withProtocolVersion($version);
    }

    /**
     * @return array|\string[][]
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    /**
     * @param string $name
     * @return array|string[]
     */
    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * @param string $name
     * @param $value
     * @return MessageInterface
     */
    public function withHeader(
        string $name,
               $value
    ): MessageInterface
    {
        return $this->response->withHeader($name, $value);
    }

    /**
     * @param string $name
     * @param $value
     * @return MessageInterface
     */
    public function withAddedHeader(
        string $name,
               $value
    ): MessageInterface
    {
        return $this->response->withAddedHeader($name, $value);
    }

    /**
     * @param string $name
     * @return MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface
    {
        return $this->response->withoutHeader($name);
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    /**
     * @param StreamInterface $body
     * @return MessageInterface
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->response->withBody($body);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function withStatus(
        int $code,
        string $reasonPhrase = ''
    ): ResponseInterface
    {
        return $this->response->withStatus($code, $reasonPhrase);
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }
}