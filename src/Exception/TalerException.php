<?php

namespace Taler\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;

class TalerException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param ResponseInterface|null $response
     */
    public function __construct(
        string     $message = "",
        int        $code = 0,
        ?\Throwable $previous = null,
        private ?ResponseInterface $response = null
    )
    {
        $message = static::sanitize($message);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param string $message
     * @return string
     */
    protected static function sanitize(string $message): string
    {
        $patterns = [
            '/(Authorization:?\s*(?:Bearer|Basic)\s+)[^\s]+/i',
            '/\b(secret|access_token|api[_-]?key|token|client_secret|password|pwd)\s*[:=]\s*[^&\s]+/i',
        ];
        $replacements = ['$1***', '$1=***'];
        return (string) preg_replace($patterns, $replacements, $message);
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return string|null
     */
    public function getRawResponseBody(): ?string
    {
        return $this->response ? (string) $this->response->getBody() : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponseJson(): ?array
    {
        if (!$this->response) return null;
        return json_decode((string) $this->response->getBody(), true);
    }
}