<?php

namespace ComissionCalculator\Exceptions;

use Exception;

class InvalidTransactionsFileException extends Exception
{
    protected $message = 'Invalid transactions file provided.';
}
