<?php

namespace Taler\Api\TokenFamilies;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\TokenFamilies\Actions\CreateTokenFamily;
use Taler\Api\TokenFamilies\Dto\TokenFamilyCreateRequest;
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
}


