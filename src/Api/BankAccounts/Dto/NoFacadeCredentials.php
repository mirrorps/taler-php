<?php

namespace Taler\Api\BankAccounts\Dto;

class NoFacadeCredentials implements FacadeCredentials
{
    public readonly string $type;

    public function __construct()
    {
        $this->type = 'none';
    }
}


