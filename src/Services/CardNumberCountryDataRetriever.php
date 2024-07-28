<?php

namespace ComissionCalculator\Services;

use ComissionCalculator\Exceptions\InvalidCountryDataException;
use ComissionCalculator\Exceptions\InvalidCountryDataTypeException;
use ComissionCalculator\Exceptions\InvalidUrlException;
use ComissionCalculator\Exceptions\MissingCountryDataResponseKeyException;
use ComissionCalculator\Structures\CountryData;
use ComissionCalculator\Wrappers\FilesystemWrapper;

class CardNumberCountryDataRetriever
{
    private const REQUIRED_RESPONSE_COUNTRY_KEY = 'country';
    private const REQUIRED_RESPONSE_ALPHA_KEY = 'alpha2';

    /** @var FilesystemWrapper */
    private FilesystemWrapper $filesystem;

    /** @var string */
    private string $binListProviderUrl;

    public function __construct(FilesystemWrapper $filesystem, string $binListProviderUrl)
    {
        $this->filesystem = $filesystem;
        $this->binListProviderUrl = $binListProviderUrl;
    }

    /**
     * @param string $bin
     * @return CountryData
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function getCountryData(string $bin): CountryData
    {
        if (!$this->binListProviderUrl || filter_var($this->binListProviderUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException($this->binListProviderUrl);
        }

        $countryData = $this->filesystem->fileGetContents($this->binListProviderUrl . '/' . $bin);

        if (!$countryData) {
            throw new InvalidCountryDataTypeException();
        }

        $arrayCountryData = json_decode($countryData, true);

        if (!is_array($arrayCountryData)) {
            throw new InvalidCountryDataTypeException();
        }

        $this->getAlphaCode($arrayCountryData);

        $countryData = new CountryData();
        $countryData->countryCode = $this->getAlphaCode($arrayCountryData);
        // @todo: set isEU

        return $countryData;
    }

    /**
     * @param array $arrayCountryData
     * @return string
     * @throws MissingCountryDataResponseKeyException
     */
    private function getAlphaCode(array $arrayCountryData): string
    {
        $countryKey = self::REQUIRED_RESPONSE_COUNTRY_KEY;
        $alphaKey = self::REQUIRED_RESPONSE_ALPHA_KEY;

        if (
            !array_key_exists($countryKey, $arrayCountryData)
            || !is_array($arrayCountryData[$countryKey])
            || !array_key_exists($alphaKey, $arrayCountryData[$countryKey])
        ) {
            throw new MissingCountryDataResponseKeyException("$countryKey.$alphaKey");
        }

        return (string)$arrayCountryData[$countryKey][$alphaKey];
    }
}
