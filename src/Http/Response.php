<?php

namespace Taler\Http;

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
     * @return ResponseInterface
     */
    public function withProtocolVersion(string $version): ResponseInterface
    {
        $new = clone $this;
        $new->response = $this->response->withProtocolVersion($version);
        return $new;
    }

    /**
     * @return array<string, array<int, string>>
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
     * @return array<int, string>
     */
    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    /**
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * @param string $name
     * @param string|array<int, string> $value
     * @return ResponseInterface
     */
    public function withHeader(string $name, $value): ResponseInterface
    {
        $new = clone $this;
        $new->response = $this->response->withHeader($name, $value);
        return $new;
    }

    /**
     * @param string $name
     * @param string|array<int, string> $value
     * @return ResponseInterface
     */
    public function withAddedHeader(string $name, $value): ResponseInterface
    {
        $new = clone $this;
        $new->response = $this->response->withAddedHeader($name, $value);
        return $new;
    }

    /**
     * @param string $name
     * @return ResponseInterface
     */
    public function withoutHeader(string $name): ResponseInterface
    {
        $new = clone $this;
        $new->response = $this->response->withoutHeader($name);
        return $new;
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
     * @return ResponseInterface
     */
    public function withBody(StreamInterface $body): ResponseInterface
    {
        $new = clone $this;
        $new->response = $this->response->withBody($body);
        return $new;
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
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->response = $this->response->withStatus($code, $reasonPhrase);
        return $new;
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }
}