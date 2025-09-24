<?php

namespace Taler\Http;

use League\Uri\Uri;
use Taler\Http\Response as TalerResponse;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PsrDiscovery\Discover;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Taler\Helpers\sanitizeString;

class HttpClientWrapper
{
    /** @var string */
    protected $userAgent = 'Mirrorps_Taler_PHP (https://github.com/mirrorps/taler-php)';

    /**
     * @var array<int, string>
     */
    private array $sensitiveKeys = [
		'authorization', 'access_token', 'refresh_token', 'id_token', 'jwt',
		'token', 'api_key', 'api-key', 'client_secret', 'password', 'pwd',
		'merchant_sig', 'lpt', 'session', 'session_id'
	];

    /**
	 * @param TalerConfig $config
	 * @param ClientInterface|null $client
	 * @param bool $wrapResponse
	 */
	public function __construct(
		private TalerConfig $config,
		private ?ClientInterface $client = null,
		private ?LoggerInterface $logger = null,
		private ?RequestFactoryInterface $requestFactory = null,
		private ?StreamFactoryInterface $streamFactory = null,
		public bool $wrapResponse = true
	)
	{
		$this->client = $client ?? Discover::httpClient();
		$this->logger = $logger ?? new NullLogger();
		$this->requestFactory = $requestFactory ?? Discover::httpRequestFactory();
		$this->streamFactory = $streamFactory ?? Discover::httpStreamFactory();

		if (!$this->client || !$this->requestFactory || !$this->streamFactory) {
			throw new \RuntimeException(
				'Required PSR-18 HTTP Client or PSR-17 Factory implementations not found. ' .
				'Please install a compatible package (e.g., guzzlehttp/guzzle) or provide your own implementations.'
			);
		}

	}

	/**
	 * Send a HTTP request.
	 *
	 * @param string $method HTTP method (GET, POST, etc.)
	 * @param string $endpoint Endpoint path (relative to base URL)
	 * @param array<string, string|string[]> $headers
	 * @param string|null $body Request body, if any
	 * @return ResponseInterface
	 */
	public function request(
		string $method,
		string $endpoint,
		array $headers = [],
		?string $body = null
	): ResponseInterface
	{
		$request = $this->createRequest($method, $endpoint, $headers, $body);
        
        if ($this->config->isDebugLoggingEnabled()) {
            $this->logRequest($request);
        }

		try {
			$response = $this->client->sendRequest($request);
		
            if ($this->config->isDebugLoggingEnabled()) {
                $this->logResponse($response);
            }

			if ($this->wrapResponse) {
				return new TalerResponse($response);
			}

			return $response;
		} catch (\Throwable $e) {

			$sanitizedMessage = sanitizeString((string) $e->getMessage());
			$this->logger->error("Taler request failed: {$e->getCode()}, {$sanitizedMessage}");

			if ($this->wrapResponse) {
				throw new TalerException($e->getMessage(), $e->getCode());
			}

			throw $e;
		}
	}

	/**
	 * Send a HTTP request.
	 *
	 * @param string $method HTTP method (GET, POST, etc.)
	 * @param string $endpoint Endpoint path (relative to base URL)
	 * @param array<string, string|string[]> $headers Additional headers
	 * @param string|null $body Request body, if any
	 * @return mixed
	 */
	public function requestAsync(
		string $method,
		string $endpoint,
		array $headers = [],
		?string $body = null
	)
	{
		if (!$this->client instanceof \Http\Client\HttpAsyncClient) {
			throw new \RuntimeException(
				'The provided HTTP client does not support async requests.'
			);
		}

		$request = $this->createRequest($method, $endpoint, $headers, $body);
		
        if ($this->config->isDebugLoggingEnabled()) {
            $this->logRequest($request);
        }
		
		try {
			return $this->client->sendAsyncRequest($request); // @phpstan-ignore-line: $this->client is guaranteed to be an async client by the instanceof check above
		} catch (\Throwable $e) {

			$sanitizedMessage = sanitizeString((string) $e->getMessage());
			$this->logger->error("Taler request failed: {$e->getCode()}, {$sanitizedMessage}");

			if ($this->wrapResponse) {
				throw new TalerException($e->getMessage(), $e->getCode());
			}

			throw $e;
		}
	}

	/**
	 * Creates a HTTP request.
	 *
	 * @param string $method HTTP method (GET, POST, etc.)
	 * @param string $endpoint Endpoint path (relative to base URL)
	 * @param array<string, string|string[]> $headers Additional headers
	 * @param string|null $body Request body, if any
	 * @return RequestInterface
	 */

	private function createRequest(
		string $method,
		string $endpoint,
		array $headers,
		?string $body = null
	): RequestInterface
	{
		$url = $this->buildUrl($endpoint);

		$headers['User-Agent'] = $this->userAgent;

		if ($authToken = $this->config->getAuthToken()) {
			$headers['Authorization'] = $authToken;
		}

		if ($body !== null && !isset($headers['Content-Type'])) {
			$headers['Content-Type'] = 'application/json';
		}

		$request = $this->requestFactory->createRequest($method, $url);

		foreach ($headers as $name => $value) {
			// Defensive: strip CR/LF to prevent header injection/log-poisoning
			$safeName = str_replace(["\r", "\n"], '', (string) $name);
			if (is_array($value)) {
				$cleanValues = [];
				foreach ($value as $v) {
					$cleanValues[] = str_replace(["\r", "\n"], '', (string) $v);
				}
				$request = $request->withHeader($safeName, $cleanValues);
			} else {
				$request = $request->withHeader($safeName, str_replace(["\r", "\n"], '', (string) $value));
			}
		}

		if ($body !== null) {
			$stream = $this->streamFactory->createStream($body);
			$request = $request->withBody($stream);
		}

		return $request;
	}

	private function buildUrl(string $endpoint): string
	{
		try {
			$endpoint = $this->encodeEndpointPath($endpoint);
			// Resolve the endpoint URI against the base URI.
			// Uri::fromBaseUri handles path normalization (e.g., removing dot segments like "/./", "/../")
			// and resolves the endpoint relative to the base URI according to RFC 3986.
			$finalUrl = Uri::fromBaseUri($endpoint, $this->getBaseUrl());

			$this->validateFinalUrl($endpoint, $finalUrl);

			return $finalUrl->__toString();

		} catch (\League\Uri\Contracts\UriException | \InvalidArgumentException $e) {
			throw new TalerException('Failed to build URL: ' . $e->getMessage(), 0, $e);
		}
	}

	/**
	 * Ensure each path segment of the provided endpoint is safely URL-encoded.
	 * This prevents path injection and guarantees reserved characters within
	 * variables are encoded instead of interpreted as path delimiters.
	 */
	private function encodeEndpointPath(string $endpoint): string
	{
		// Reject absolute or scheme-based endpoints (must be relative to base)
		if (preg_match('/^[A-Za-z][A-Za-z0-9+.-]*:\/\//', $endpoint) === 1 || str_starts_with($endpoint, '//')) {
			throw new \InvalidArgumentException('Absolute URLs are not allowed in endpoints.');
		}

		// Split query from path (we keep query untouched as callers already use http_build_query)
		$questionPos = strpos($endpoint, '?');
		$pathOnly = $questionPos === false ? $endpoint : substr($endpoint, 0, $questionPos);
		$queryOnly = $questionPos === false ? '' : substr($endpoint, $questionPos);

		// Normalize leading/trailing slashes are preserved; encode per segment
		$leadingSlash = str_starts_with($pathOnly, '/');
		$trailingSlash = $pathOnly !== '' && str_ends_with($pathOnly, '/');
		$segments = $pathOnly === '' ? [] : explode('/', trim($pathOnly, '/'));
		$encodedSegments = [];
		foreach ($segments as $seg) {
			if ($seg === '') { continue; }
			$decoded = rawurldecode($seg);
			// Reject encoded slashes within a segment
			if (strpos($decoded, '/') !== false) {
				throw new \InvalidArgumentException('Encoded slashes are not allowed in endpoints.');
			}
			$encodedSegments[] = rawurlencode($decoded);
		}

		$rebuiltPath = implode('/', $encodedSegments);
		if ($leadingSlash) {
			$rebuiltPath = '/' . $rebuiltPath;
		}
		if ($trailingSlash && $rebuiltPath !== '') {
			$rebuiltPath .= '/';
		}

		return $rebuiltPath . $queryOnly;
	}

	private function validateFinalUrl(string $endpoint, Uri $finalUrl): void
	{
		if (strpos($endpoint, '%2F') !== false || strpos($endpoint, '%2f') !== false) {
			throw new \InvalidArgumentException('Encoded slashes are not allowed in endpoints.');
		}

        // Ensure the resolved URI is still within the original base URI's scheme/authority and path prefix with segment boundary.
        $base = Uri::new($this->getBaseUrl());

        // Check scheme/host/port containment
        if ($finalUrl->getScheme() !== $base->getScheme()
            || $finalUrl->getHost() !== $base->getHost()
            || $finalUrl->getPort() !== $base->getPort()) {
            throw new \InvalidArgumentException('Endpoint results in a URL outside the configured base authority.');
        }

        // Path boundary check: final path must equal base path or start with base path followed by '/'
        $basePath = $base->getPath();
        $finalPath = $finalUrl->getPath();
        $basePathTrimmed = rtrim($basePath, '/');

        if ($basePathTrimmed === '') {
            // Base path is root; any absolute path is acceptable
            if ($finalPath === '' || $finalPath[0] !== '/') {
                throw new \InvalidArgumentException('Resolved path is not under the configured base path.');
            }
            return;
        }

        $validSame = ($finalPath === $basePathTrimmed);
        $validSub = (strpos($finalPath, $basePathTrimmed . '/') === 0);
        if (!$validSame && !$validSub) {
            throw new \InvalidArgumentException('Endpoint results in a URL outside the configured base path.');
        }
	}

	private function getBaseUrl(): string
	{
		return rtrim($this->config->getBaseUrl(), '/') . '/';
	}

	private function sanitizeUri(string $uri): string
	{
        // Parse and rebuild the URL while redacting userinfo and sensitive query params
        $parts = @parse_url($uri);
        if ($parts === false) {
            return preg_replace('#//[^/@:]+:[^/@]*@#', '//***:***@', $uri) ?? $uri;
        }

        $scheme = $parts['scheme'] ?? null;
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        $path = $parts['path'] ?? '';
        $fragment = $parts['fragment'] ?? null;

        // Redact sensitive query parameters
        $queryString = '';
        if (isset($parts['query'])) {
            $params = [];
            parse_str((string) $parts['query'], $params);
            $sensitive = $this->sensitiveKeys;
            $redactedParams = [];
            foreach ($params as $key => $value) {
                $lk = strtolower((string) $key);
                if (in_array($lk, $sensitive, true)) {
                    if (is_array($value)) {
                        $redactedParams[$key] = array_fill(0, count($value), '***');
                    } else {
                        $redactedParams[$key] = '***';
                    }
                } else {
                    $redactedParams[$key] = $value;
                }
            }
            $queryString = http_build_query($redactedParams, '', '&', PHP_QUERY_RFC3986);
        }

        // Rebuild without userinfo
        $schemePart = $scheme !== null ? $scheme . '://' : '';
        $authority = $host !== '' ? $host : '';
        if ($port !== null && $port > 0) {
            $authority .= ':' . $port;
        }

        $rebuilt = $schemePart . $authority . $path;
        if ($queryString !== '') {
            $rebuilt .= '?' . $queryString;
        }
        if ($fragment !== null && $fragment !== '') {
            $rebuilt .= '#' . $fragment;
        }

        // Fallback redact any leftover userinfo patterns
        return preg_replace('#//[^/@:]+:[^/@]*@#', '//***:***@', $rebuilt) ?? $rebuilt;
	}

	/**
	 * @param array<string, array<int, string>> $headers
	 * @param array<int, string> $sensitiveLower
	 * @return array<string, array<int, string>>
	 */
	private function redactHeaders(array $headers, array $sensitiveLower): array
	{
		$redacted = [];
		foreach ($headers as $name => $values) {
			$lname = strtolower($name);
			if (in_array($lname, $sensitiveLower, true)) {
				$redacted[$name] = ['***'];
			} else {
				$redacted[$name] = $values;
			}
		}
		return $redacted;
	}

    /**
     * @param array<string, string|array<int, string>> $headers
     */
    private function isJsonContentType(array $headers): bool
	{
		$contentTypes = $headers['Content-Type'] ?? $headers['content-type'] ?? [];
		$first = is_array($contentTypes) ? ($contentTypes[0] ?? '') : (string) $contentTypes;
		$type = strtolower(trim(explode(';', $first)[0]));
		return $type === 'application/json' || (function_exists('str_ends_with') ? str_ends_with($type, '+json') : substr($type, -5) === '+json');
	}

	private function sanitizeJsonData(mixed $data): mixed
	{
		if (is_array($data)) {
			$sanitized = [];
			foreach ($data as $key => $value) {
				$lowerKey = is_string($key) ? strtolower($key) : $key;
                if (in_array($lowerKey, $this->sensitiveKeys, true)) {
					$sanitized[$key] = '***';
				} else {
					$sanitized[$key] = $this->sanitizeJsonData($value);
				}
			}
			return $sanitized;
		}
		if ($data instanceof \stdClass) {
			foreach (get_object_vars($data) as $k => $v) {
				$lowerKey = strtolower((string) $k);
                if (in_array($lowerKey, $this->sensitiveKeys, true)) {
					$data->$k = '***';
				} else {
					$data->$k = $this->sanitizeJsonData($v);
				}
			}
			return $data;
		}
		return $data;
	}

	private function getResponseBodyPreview(ResponseInterface $response, int $maxBytes = 4096): ?string
	{
		$stream = $response->getBody();
		if ($stream->isSeekable()) {
			$pos = $stream->tell();
			try {
				$stream->rewind();
			} catch (\Throwable) {
				// ignore
			}
			$contents = (string) $stream->getContents();
			// restore pointer
			try {
				$stream->seek($pos);
			} catch (\Throwable) {
				// ignore
			}
		} else {
			// Avoid consuming non-seekable streams
			return null;
		}

		if ($contents === '') {
			return '';
		}

		$headers = $response->getHeaders();
		if ($this->isJsonContentType($headers)) {
			$decoded = json_decode($contents);
			if (json_last_error() === JSON_ERROR_NONE) {
				$sanitized = $this->sanitizeJsonData($decoded);
				$reencoded = json_encode($sanitized);
				$contents = is_string($reencoded) ? $reencoded : $contents;
			} else {
				$contents = sanitizeString($contents);
			}
		} else {
			$contents = sanitizeString($contents);
		}

		if (strlen($contents) > $maxBytes) {
			return substr($contents, 0, $maxBytes) . ' [truncated]';
		}
		return $contents;
	}

	private function logRequest(RequestInterface $request): void
	{
		$sanitizedUri = $this->sanitizeUri((string) $request->getUri());
        $headers = $this->redactHeaders($request->getHeaders(), [
            'authorization', 'proxy-authorization', 'cookie', 'set-cookie', 'referer',
            'x-api-key', 'x-auth-token', 'x-csrf-token', 'x-xsrf-token', 'apikey',
            'authentication', 'x-client-secret', 'signature', 'digest'
        ]);

		$this->logger->debug('Taler request: ' . $sanitizedUri . ', ' . $request->getMethod());
		$this->logger->debug('Taler request headers: ', $headers);
	}

	private function logResponse(ResponseInterface $response): void
	{
        $headers = $this->redactHeaders($response->getHeaders(), [
            'authorization', 'proxy-authorization', 'cookie', 'set-cookie', 'referer',
            'x-api-key', 'x-auth-token', 'x-csrf-token', 'x-xsrf-token', 'apikey',
            'authentication', 'x-client-secret', 'signature', 'digest'
        ]);
        // Sanitize any URL-like headers such as Location/Content-Location
        foreach (['Location', 'Content-Location'] as $hname) {
            if (isset($headers[$hname])) {
                $sanitizedValues = [];
                foreach ($headers[$hname] as $v) {
                    $sanitizedValues[] = $this->sanitizeUri((string) $v);
                }
                $headers[$hname] = $sanitizedValues;
            }
        }

		$this->logger->debug('Taler response: ' . $response->getStatusCode() . ', ' . $response->getReasonPhrase());
		$this->logger->debug('Taler response headers: ', $headers);
		$preview = $this->getResponseBodyPreview($response);
		if ($preview !== null) {
			$this->logger->debug('Taler response body: ' . $preview);
		}
	}

}
