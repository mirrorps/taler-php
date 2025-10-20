<?php

namespace Taler\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Taler\Api\Dto\ErrorDetail;

use function Taler\Helpers\sanitizeString;

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
        $message = sanitizeString($message);
        parent::__construct($message, $code, $previous);
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

    /**
     * Parse the HTTP response body into an ErrorDetail DTO.
     *
     * @return ErrorDetail|null
     */
    public function getResponseDTO(): mixed
    {
        $json = $this->getResponseJson();
        if (!is_array($json)) {
            return null;
        }
        
        /** @var array{
         * code: int,
         * hint: string,
         * detail: string,
         * parameter: string,
         * path: string,
         * offset: string,
         * index: string,
         * object: string,
         * currency: string,
         * type_expected: string,
         * type_actual: string,
         * extra: array<string, mixed>
         * } $json
         */
        return ErrorDetail::fromArray($json);
    }
}