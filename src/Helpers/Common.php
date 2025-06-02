<?php

namespace Taler\Helpers;

if (!function_exists('isValidBaseUrl')) {
    function isValidBaseUrl(string $url): bool
    {
        // Remove illegal characters from the URL
        $sanitizedUrl = filter_var($url, FILTER_SANITIZE_URL);

        if ($sanitizedUrl === false) {
            return false;
        }

        if (filter_var($sanitizedUrl, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Restrict to https only
        $scheme = parse_url($sanitizedUrl, PHP_URL_SCHEME);
        if ($scheme === false) {
            return false;
        }


        if ($scheme !== 'https') {
            return false;
        }

        return true;
    }
}