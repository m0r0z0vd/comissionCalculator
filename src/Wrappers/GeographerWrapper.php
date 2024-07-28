<?php

namespace CommissionApp\Wrappers;

use MenaraSolutions\Geographer\Earth;

class GeographerWrapper
{
    /**
     * @return string[]
     */
    public function getEuropeCountryCodes(): array
    {
        return (new Earth())->getEurope()->pluck('code');
    }
}
