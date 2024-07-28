<?php

namespace ComissionCalculator\Exceptions;

use Throwable;

class MissingRatesDataKeyException extends InvalidRatesDataException
{
    public function __construct(
        string $key,
        $code = 0,
        Throwable $previous = null
    ) {
        $message = 'Currency rates data does not contain all the required keys.';

        if ($key) {
            $message .= ' Missing key: ' . $key;
        }

        parent::__construct($message, $code, $previous);
    }
}
