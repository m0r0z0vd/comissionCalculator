<?php

namespace CommissionApp\Exceptions;

use Exception;
use Throwable;

class ExchangeRateNotFoundException extends Exception
{
    public function __construct(string $currencyCode = "", int $code = 0, Throwable $previous = null)
    {
        $message = 'Exchange rate for not found.';

        if ($currencyCode) {
            $message .= ' Currency: ' . $currencyCode;
        }

        parent::__construct($message, $code, $previous);
    }
}
