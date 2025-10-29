<?php

namespace Taler\Api\TwoFactorAuth\Dto;

/**
 * TAN delivery channels.
 *
 * @since v21
 */
class TanChannel
{
    /**
     * TAN sent via SMS.
     */
    public const SMS = 'sms';

    /**
     * TAN sent via e-mail.
     */
    public const EMAIL = 'email';
}


