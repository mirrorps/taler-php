<?php

namespace Taler\Exception;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\OutOfStockResponse;

/**
 * Exception representing HTTP 410 Gone for out-of-stock cases.
 * Carries the original response and exposes a typed DTO parser.
 */
class OutOfStockException extends TalerException
{
    /**
     * @param string $message Human-readable error message
     * @param \Throwable|null $previous Previous exception
     * @param ResponseInterface|null $response Optional HTTP response
     */
    public function __construct(
        string $message = 'Product out of stock',
        ?\Throwable $previous = null,
        ?ResponseInterface $response = null
    ) {
        parent::__construct(
            message: $message,
            code: 410,
            previous: $previous,
            response: $response
        );
    }

    /**
     * Parse the HTTP response body into an OutOfStockResponse DTO.
     *
     * @return OutOfStockResponse|null
     */
    public function getResponseDTO(): OutOfStockResponse|null
    {
        $json = $this->getResponseJson();
        if (!is_array($json)) {
            return null;
        }

        /**
         * @var array{
         *   product_id: string,
         *   requested_quantity: int,
         *   available_quantity: int,
         *   restock_expected?: array{t_s: int|string}
         * } $json
         */
        return OutOfStockResponse::createFromArray($json);
    }
}


