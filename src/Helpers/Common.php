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

        // Get host from URL
        $host = parse_url($sanitizedUrl, PHP_URL_HOST);
        if ($host === false || $host === null) {
            return false;
        }

        return true;
    }
}