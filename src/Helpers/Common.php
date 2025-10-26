<?php

namespace Taler\Helpers;

if (!function_exists('isValidUrl')) {
    function isValidUrl(string $url, bool $httpsOnly = true): bool
    {
        // Remove illegal characters from the URL
        $sanitizedUrl = filter_var($url, FILTER_SANITIZE_URL);

        if ($sanitizedUrl === false) {
            return false;
        }

        if (filter_var($sanitizedUrl, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($sanitizedUrl, PHP_URL_SCHEME);
        if ($scheme === false) {
            return false;
        }

        if ($httpsOnly === true && $scheme !== 'https') {
            return false;
        }

        // Disallow userinfo (user:pass@) in URLs
        $user = parse_url($sanitizedUrl, PHP_URL_USER);
        $pass = parse_url($sanitizedUrl, PHP_URL_PASS);
        if ($user !== null || $pass !== null) {
            return false;
        }

        // Get host from URL
        $host = parse_url($sanitizedUrl, PHP_URL_HOST);
        if ($host === false || $host === null) {
            return false;
        }

        return true;
    }
}

if (!function_exists('sanitizeString')) {

    /**
     * @param string $message
     * @return string
     */
    function sanitizeString(string $message): string
    {
        $patterns = [
            '/(Authorization:?\s*(?:Bearer|Basic)\s+)[^\s]+/i',
            '/\b(secret|access_token|refresh_token|id_token|jwt|api[_-]?key|token|client_secret|password|pwd|session|session_id)\s*[:=]\s*[^&\s]+/i',
        ];
        $replacements = ['$1***', '$1=***'];
        return (string) preg_replace($patterns, $replacements, $message);
    }
}

if (!function_exists('parseLibtoolVersion')) {
    /**
     * Parse Taler versioning triplet "current:revision:age".
     * Returns [current, revision, age] or null on invalid input.
     *
     * @return array{0:int,1:int,2:int}|null
     */
    function parseLibtoolVersion(string $version): ?array
    {
        $parts = explode(':', trim($version));
        if (count($parts) !== 3) {
            return null;
        }

        foreach ($parts as $p) {
            if ($p === '' || preg_match('/^\d+$/', $p) !== 1) {
                return null;
            }
        }

        return [(int) $parts[0], (int) $parts[1], (int) $parts[2]];
    }
}

if (!function_exists('isProtocolCompatible')) {
    /**
     * Determine if a client's expected current version is supported by a server
     * advertising Taler versioning triplet (current:revision:age).
     * Compatibility holds if clientCurrent is within [serverCurrent - serverAge, serverCurrent].
     */
    function isProtocolCompatible(int $serverCurrent, int $serverAge, int $clientCurrent): bool
    {
        return $clientCurrent <= $serverCurrent && $clientCurrent >= ($serverCurrent - $serverAge);
    }
}