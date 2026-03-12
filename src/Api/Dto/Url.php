<?php

namespace Taler\Api\Dto;

class Url implements \JsonSerializable
{
    private function __construct(
        private readonly string $value
    ) {}

    public static function fromString(string $value): self
    {
        if ($value === '' || trim($value) === '') {
            throw new \InvalidArgumentException('url must not be empty');
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('url must be a valid URL');
        }

        $parts = parse_url($value);
        $scheme = strtolower($parts['scheme'] ?? '');

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException('url scheme must be http or https');
        }

        if (!isset($parts['host']) || $parts['host'] === '') {
            throw new \InvalidArgumentException('url must include a host');
        }

        return new self($value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
