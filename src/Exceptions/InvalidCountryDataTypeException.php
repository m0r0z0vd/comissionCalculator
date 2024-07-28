<?php

namespace ComissionCalculator\Exceptions;

class InvalidCountryDataTypeException extends InvalidCountryDataException
{
    protected $message = 'Country data must be a valid JSON string.';
}
