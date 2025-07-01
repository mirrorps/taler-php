<?php

namespace Taler\Api\Order\Dto;

class StatusGotoResponse
{
    /**
     * @param string $public_reorder_url The client should go to the reorder URL, there a fresh order might be created
     */
    public function __construct(
        public readonly string $public_reorder_url
    ) {}

    /**
     * @param array{
     *     public_reorder_url: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['public_reorder_url']
        );
    }
} 