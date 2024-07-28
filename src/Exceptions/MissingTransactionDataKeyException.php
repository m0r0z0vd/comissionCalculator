<?php

namespace ComissionCalculator\Exceptions;

use Throwable;

class MissingTransactionDataKeyException extends UnprocessableTransactionDataException
{
    private const DEFAULT_MESSAGE = 'Transaction data does not contain all the required keys.';

    public function __construct(
        string $key,
        string $message = self::DEFAULT_MESSAGE,
        $code = 0,
        Throwable $previous = null
    ) {
        if ($key) {
            $message = self::DEFAULT_MESSAGE . ' Missing key: ' . $key;
        }

        parent::__construct($message, $code, $previous);
    }
}
