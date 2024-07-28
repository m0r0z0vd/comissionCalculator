<?php

namespace CommissionApp\Exceptions;

class InvalidTransactionTypeException extends UnprocessableTransactionDataException
{
    protected $message = 'Transaction data must be a valid JSON string.';
}
