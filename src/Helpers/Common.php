<?php
if (!function_exists('isValidBaseUrl')) {
    function isValidBaseUrl(string $url): bool
    {
        // Remove illegal characters from the URL
        $sanitizedUrl = filter_var($url, FILTER_SANITIZE_URL);

        if (filter_var($sanitizedUrl, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Restrict to https only
        $scheme = parse_url($sanitizedUrl, PHP_URL_SCHEME);
        if (!in_array($scheme, ['https'], true)) {
            return false;
        }

        return true;
    }
}