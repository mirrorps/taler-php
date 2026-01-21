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

if (!function_exists('isValidTalerAmount')) {
    /**
     * Validate a Taler amount string format.
     *
     * The format is: [+|-]CURRENCY:VALUE
     * - Currency: 3 to 11 ASCII letters (a-z, A-Z)
     * - Value: Decimal number with optional fractional part (up to 8 decimal digits)
     * - Integer part must be <= 2^52 (4503599627370496)
     *
     * Valid examples: "EUR:1.50", "EUR:10", "USD:0.00000001", "+EUR:5", "-CHF:3.50"
     * Invalid examples: "42" (no currency), "EUR:1." (trailing dot), "EUR:.1" (no integer part)
     *
     * @param string $amount The amount string to validate
     * @return bool True if the format is valid, false otherwise
     */
    function isValidTalerAmount(string $amount): bool
    {
        // Handle optional leading sign
        $amount = ltrim($amount, ' +-');
        if ($amount === '') {
            return false;
        }

        // Must contain exactly one colon separating currency from value
        if(substr_count($amount, ':') !== 1){
            return false;
        }

        [$currency, $value] = explode(':', $amount);

        if ($currency === '' || $value === '') {
            return false;
        }

        // Validate currency: 3 to 11 ASCII letters only
        if (strlen($currency) < 3 || strlen($currency) > 11) {
            return false;
        }
        if (!preg_match('/^[a-zA-Z]+$/', $currency)) {
            return false;
        }

        // Must not start or end with a dot
        if ($value[0] === '.' || $value[strlen($value) - 1] === '.') {
            return false;
        }

        // Split into integer and fractional parts
        $parts = explode('.', $value);
        if (count($parts) > 2) {
            return false;
        }

        $integerPart = $parts[0];
        $fractionalPart = $parts[1] ?? '';

        // Integer part must be digits only and not empty
        if ($integerPart === '' || !preg_match('/^\d+$/', $integerPart)) {
            return false;
        }

        /** @var non-empty-string&numeric-string $integerPart */

        // Fractional part (if present) must be digits only and max 8 digits
        if ($fractionalPart !== '') {
            if (!preg_match('/^\d+$/', $fractionalPart)) {
                return false;
            }
            if (strlen($fractionalPart) > 8) {
                return false;
            }
        }

        // Integer part must be <= 2^52 (4503599627370496)
        // Use bccomp for arbitrary precision comparison to avoid float issues
        $maxValue = '4503599627370496';
        if (function_exists('bccomp')) {
            if (bccomp($integerPart, $maxValue) > 0) {
                return false;
            }
        } else {
            // Fallback: compare string lengths and lexicographically
            $integerPartNormalized = ltrim($integerPart, '0') ?: '0';
            $maxValueLen = strlen($maxValue);
            $intPartLen = strlen($integerPartNormalized);

            if ($intPartLen > $maxValueLen) {
                return false;
            }
            if ($intPartLen === $maxValueLen && $integerPartNormalized > $maxValue) {
                return false;
            }
        }

        return true;
    }
}