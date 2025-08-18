<?php

namespace Taler\Api\TokenFamilies;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\TokenFamilies\Actions\CreateTokenFamily;
use Taler\Api\TokenFamilies\Actions\UpdateTokenFamily;
use Taler\Api\TokenFamilies\Actions\GetTokenFamilies;
use Taler\Api\TokenFamilies\Dto\TokenFamilyDetails;
use Taler\Api\TokenFamilies\Dto\TokenFamilyCreateRequest;
use Taler\Api\TokenFamilies\Dto\TokenFamilyUpdateRequest;
use Taler\Api\TokenFamilies\Dto\TokenFamiliesList;
use Taler\Exception\TalerException;

class TokenFamiliesClient extends AbstractApiClient
{
    /**
     * @param TokenFamilyCreateRequest $request
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function createTokenFamily(TokenFamilyCreateRequest $request, array $headers = []): void
    {
        CreateTokenFamily::run($this, $request, $headers);
    }

    /**
     * @param TokenFamilyCreateRequest $request
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createTokenFamilyAsync(TokenFamilyCreateRequest $request, array $headers = []): mixed
    {
        return CreateTokenFamily::runAsync($this, $request, $headers);
    }

    /**
     * @param string $slug
     * @param TokenFamilyUpdateRequest $request
     * @param array<string, string> $headers Optional request headers
     * @return TokenFamilyDetails|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateTokenFamily(string $slug, TokenFamilyUpdateRequest $request, array $headers = []): TokenFamilyDetails|array
    {
        return UpdateTokenFamily::run($this, $slug, $request, $headers);
    }

    /**
     * @param string $slug
     * @param TokenFamilyUpdateRequest $request
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateTokenFamilyAsync(string $slug, TokenFamilyUpdateRequest $request, array $headers = []): mixed
    {
        return UpdateTokenFamily::runAsync($this, $slug, $request, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return TokenFamiliesList|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getTokenFamilies(array $headers = []): TokenFamiliesList|array
    {
        return GetTokenFamilies::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getTokenFamiliesAsync(array $headers = []): mixed
    {
        return GetTokenFamilies::runAsync($this, $headers);
    }
}


