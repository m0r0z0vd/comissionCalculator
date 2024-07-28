<?php

namespace ComissionCalculator\Exceptions;

use Throwable;

class MissingCountryDataResponseKeyException extends InvalidCountryDataException
{
    public function __construct(
        string $key,
        $code = 0,
        Throwable $previous = null
    ) {
        $message = 'Country data response does not contain all the required keys.';

        if ($key) {
            $message .= ' Missing key: ' . $key;
        }

        parent::__construct($message, $code, $previous);
    }
}
