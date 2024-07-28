<?php

namespace CommissionApp\Exceptions;

use Throwable;

class MissingTransactionDataKeyException extends UnprocessableTransactionDataException
{
    public function __construct(
        string $key,
        $code = 0,
        Throwable $previous = null
    ) {
        $message = 'Transaction data does not contain all the required keys.';

        if ($key) {
            $message .= ' Missing key: ' . $key;
        }

        parent::__construct($message, $code, $previous);
    }
}
