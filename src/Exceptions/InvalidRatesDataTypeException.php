<?php

namespace CommissionApp\Exceptions;

class InvalidRatesDataTypeException extends InvalidRatesDataException
{
    protected $message = 'Currency rates data must be a valid JSON string.';
}
