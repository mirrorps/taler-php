<?php

namespace Taler\Exception;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Order\Dto\PaymentDeniedLegallyResponse;

/**
 * Exception representing HTTP 451 Unavailable For Legal Reasons for payment denial.
 * Carries the original response and exposes a typed DTO parser.
 */
class PaymentDeniedLegallyException extends TalerException
{
    public const HTTP_STATUS_CODE = 451;
    /**
     * @param string $message Human-readable error message
     * @param \Throwable|null $previous Previous exception
     * @param ResponseInterface|null $response Optional HTTP response
     */
    public function __construct(
        string $message = 'Payment denied for legal reasons',
        ?\Throwable $previous = null,
        ?ResponseInterface $response = null
    ) {
        parent::__construct(
            message: $message,
            code: self::HTTP_STATUS_CODE,
            previous: $previous,
            response: $response
        );
    }

    /**
     * Parse the HTTP response body into a PaymentDeniedLegallyResponse DTO.
     *
     * @return PaymentDeniedLegallyResponse|null
     */
    public function getResponseDTO(): PaymentDeniedLegallyResponse|null
    {
        $json = $this->getResponseJson();
        if (!is_array($json)) {
            return null;
        }

        /**
         * @var array{exchange_base_urls: array<int, string>} $json
         */
        return PaymentDeniedLegallyResponse::createFromArray($json);
    }
}


