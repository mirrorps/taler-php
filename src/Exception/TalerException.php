<?php

namespace Taler\Exception;

use Exception;

class TalerException extends Exception
{
    public function __construct(
        string     $message = "",
        int        $code = 0,
        ?\Throwable $previous = null
    )
    {
        $message = static::sanitize($message);
        parent::__construct($message, $code, $previous);
    }

    protected static function sanitize(string $message): string
    {
        return preg_replace('/(secret|access_token)=[a-z0-9-]+/i', '$1=***', $message);
    }

}