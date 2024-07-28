<?php

namespace ComissionCalculator\Exceptions;

use Exception;
use Throwable;

class InvalidUrlException extends Exception
{
    public function __construct(
        string $url,
        int $code = 0,
        Throwable $previous = null
    ) {
        $message = "Provided URL is not valid: $url.";
        parent::__construct($message, $code, $previous);
    }
}
