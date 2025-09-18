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

class HttpClientWrapper
{
    /** @var string */
    protected $userAgent = 'Mirrorps_Taler_PHP (https://github.com/mirrorps/taler-php)';

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
		$this->logRequest($request);

		try {
			$response = $this->client->sendRequest($request);
		
			$this->logResponse($response);

			if ($this->wrapResponse) {
				return new TalerResponse($response);
			}

			return $response;
		} catch (\Throwable $e) {

			$this->logger->error("Taler request failed: {$e->getCode()}, {$e->getMessage()}");

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
		
		$this->logRequest($request);
		
		try {
			return $this->client->sendAsyncRequest($request); // @phpstan-ignore-line: $this->client is guaranteed to be an async client by the instanceof check above
		} catch (\Throwable $e) {

			$this->logger->error("Taler request failed: {$e->getCode()}, {$e->getMessage()}");

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
			$request = $request->withHeader($name, $value);
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

	private function validateFinalUrl(string $endpoint, Uri $finalUrl): void
	{
		if (strpos($endpoint, '%2F') !== false || strpos($endpoint, '%2f') !== false) {
			throw new \InvalidArgumentException('Encoded slashes are not allowed in endpoints.');
		}

		// Ensure the resolved URI is still "under" the original base URI's scheme, authority, and path prefix.
		$baseUriForCheck = Uri::new($this->getBaseUrl());
		$baseUriPrefixString = $baseUriForCheck->withPath($baseUriForCheck->getPath())->__toString();

		if (strpos($finalUrl->__toString(), $baseUriPrefixString) !== 0) {
			 throw new \InvalidArgumentException('Endpoint results in a URL outside the configured base path. Resolved URL: ' . $finalUrl->__toString() . ', Base prefix: ' . $baseUriPrefixString);
		}
	}

	private function getBaseUrl(): string
	{
		return rtrim($this->config->getBaseUrl(), '/') . '/';
	}

	private function sanitizeUri(string $uri): string
	{
		return preg_replace('#//[^/@:]+:[^/@]*@#', '//***:***@', $uri) ?? $uri;
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

	private function isJsonContentType(array $headers): bool
	{
		$contentTypes = $headers['Content-Type'] ?? $headers['content-type'] ?? [];
		$first = is_array($contentTypes) ? ($contentTypes[0] ?? '') : (string) $contentTypes;
		$type = strtolower(trim(explode(';', $first)[0] ?? ''));
		return $type === 'application/json' || (function_exists('str_ends_with') ? str_ends_with($type, '+json') : substr($type, -5) === '+json');
	}

	private function sanitizeJsonData(mixed $data): mixed
	{
		if (is_array($data)) {
			$sanitized = [];
			foreach ($data as $key => $value) {
				$lowerKey = is_string($key) ? strtolower($key) : $key;
				if (in_array($lowerKey, ['authorization', 'access_token', 'secret', 'api_key', 'api-key', 'token', 'client_secret', 'password', 'pwd'], true)) {
					$sanitized[$key] = '***';
				} else {
					$sanitized[$key] = $this->sanitizeJsonData($value);
				}
			}
			return $sanitized;
		}
		if ($data instanceof \stdClass) {
			foreach ($data as $k => $v) {
				$lowerKey = strtolower((string) $k);
				if (in_array($lowerKey, ['authorization', 'access_token', 'secret', 'api_key', 'api-key', 'token', 'client_secret', 'password', 'pwd'], true)) {
					$data->$k = '***';
				} else {
					$data->$k = $this->sanitizeJsonData($v);
				}
			}
			return $data;
		}
		return $data;
	}

	private function sanitizeBodyString(string $body): string
	{
		$patterns = [
			'/(Authorization:?\\s*(Bearer|Basic)\\s+)[^\\s]+/i',
			'/\\b(secret|access_token|api[_-]?key|token|client_secret|password|pwd)\\s*[:=]\\s*[^\\s,]+/i',
		];
		$replacements = ['$1***', '$1=***'];
		return (string) preg_replace($patterns, $replacements, $body);
	}

	private function getRequestBodyPreview(RequestInterface $request, int $maxBytes = 4096): ?string
	{
		$stream = $request->getBody();
		if ($stream->getSize() === 0) {
			return null;
		}

		if ($stream->isSeekable()) {
			$pos = $stream->tell();
			try { $stream->rewind(); } catch (\Throwable) {}
			$contents = (string) $stream->getContents();
			try { $stream->seek($pos); } catch (\Throwable) {}
		} else {
			// Avoid consuming non-seekable streams
			return null;
		}

		if ($contents === '') {
			return '';
		}

		$headers = $request->getHeaders();
		if ($this->isJsonContentType($headers)) {
			$decoded = json_decode($contents);
			if (json_last_error() === JSON_ERROR_NONE) {
				$sanitized = $this->sanitizeJsonData($decoded);
				$reencoded = json_encode($sanitized);
				$contents = is_string($reencoded) ? $reencoded : $contents;
			} else {
				$contents = $this->sanitizeBodyString($contents);
			}
		} else {
			$contents = $this->sanitizeBodyString($contents);
		}

		if (strlen($contents) > $maxBytes) {
			return substr($contents, 0, $maxBytes) . ' [truncated]';
		}
		return $contents;
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
				$contents = $this->sanitizeBodyString($contents);
			}
		} else {
			$contents = $this->sanitizeBodyString($contents);
		}

		if (strlen($contents) > $maxBytes) {
			return substr($contents, 0, $maxBytes) . ' [truncated]';
		}
		return $contents;
	}

	private function logRequest(RequestInterface $request): void
	{
		$sanitizedUri = $this->sanitizeUri((string) $request->getUri());
		$headers = $this->redactHeaders($request->getHeaders(), ['authorization', 'cookie']);

		$this->logger->debug('Taler request: ' . $sanitizedUri . ', ' . $request->getMethod());
		$this->logger->debug('Taler request headers: ', $headers);
	}

	private function logResponse(ResponseInterface $response): void
	{
		$headers = $this->redactHeaders($response->getHeaders(), ['set-cookie']);

		$this->logger->debug('Taler response: ' . $response->getStatusCode() . ', ' . $response->getReasonPhrase());
		$this->logger->debug('Taler response headers: ', $headers);
		$preview = $this->getResponseBodyPreview($response);
		if ($preview !== null) {
			$this->logger->debug('Taler response body: ' . $preview);
		}
	}

}
