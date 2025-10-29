<?php

namespace Taler\Api\DonauCharity\Dto;

use function Taler\Helpers\isValidUrl;

/**
 * Request DTO for linking a Donau charity to an instance.
 *
 * Docs shape:
 * interface PostDonauRequest {
 *   donau_url: string;
 *   charity_id: Integer;
 * }
 */
class PostDonauRequest implements \JsonSerializable
{
    /**
     * @param string $donau_url Base URL of the Donau service hosting the charity (https)
     * @param int $charity_id Numeric charity identifier inside the Donau service
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $donau_url,
        public readonly int $charity_id,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{donau_url: string, charity_id: int} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            donau_url: $data['donau_url'],
            charity_id: $data['charity_id']
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if ($this->donau_url === '' || trim($this->donau_url) === '') {
            throw new \InvalidArgumentException('donau_url must not be empty');
        }
        if (!isValidUrl($this->donau_url, true)) {
            throw new \InvalidArgumentException('donau_url must be a valid https URL');
        }
        if ($this->charity_id < 0) {
            throw new \InvalidArgumentException('charity_id must be a non-negative integer');
        }
    }

    /**
     * @return array{donau_url: string, charity_id: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'donau_url' => $this->donau_url,
            'charity_id' => $this->charity_id,
        ];
    }
}



