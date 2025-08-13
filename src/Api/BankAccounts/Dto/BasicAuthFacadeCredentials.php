<?php

namespace Taler\Api\BankAccounts\Dto;

class BasicAuthFacadeCredentials implements FacadeCredentials
{
    public readonly string $type;

    public function __construct(
        public readonly string $username,
        public readonly string $password
    ) {
        $this->type = 'basic';
    }
}


