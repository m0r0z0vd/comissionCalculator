<?php

namespace CommissionApp\Services;

use CommissionApp\Exceptions\InvalidRatesDataException;
use CommissionApp\Exceptions\InvalidRatesDataTypeException;
use CommissionApp\Exceptions\InvalidUrlException;
use CommissionApp\Exceptions\MissingRatesDataKeyException;
use CommissionApp\Wrappers\FilesystemWrapper;

class CurrencyRatesRetriever
{
    private const RATES_KEY = 'rates';

    private const BASE_CURRENCY = 'EUR';

    /** @var FilesystemWrapper */
    private FilesystemWrapper $filesystemWrapper;

    /** @var string */
    private string $providerUrl;

    /** @var string */
    private string $accessKey;

    public function __construct(
        FilesystemWrapper $filesystemWrapper,
        string $providerUrl,
        string $accessKey
    ) {
        $this->filesystemWrapper = $filesystemWrapper;
        $this->providerUrl = $providerUrl;
        $this->accessKey = $accessKey;
    }

    /**
     * @return array<string, float>
     * @throws InvalidUrlException
     * @throws InvalidRatesDataException
     */
    public function getLatestRates(): array
    {
        $url = $this->generateLatestRatesUrl();

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidUrlException($url);
        }

        $rates = $this->filesystemWrapper->fileGetContents($url);

        if (!$rates) {
            throw new InvalidRatesDataTypeException();
        }

        $rates = json_decode($rates, true);

        if (!is_array($rates)) {
            throw new InvalidRatesDataTypeException();
        }

        if (
            !array_key_exists(self::RATES_KEY, $rates)
            || !is_array($rates[self::RATES_KEY])
        ) {
            throw new MissingRatesDataKeyException(self::RATES_KEY);
        }

        return $rates[self::RATES_KEY];
    }

    /**
     * @return string
     */
    public function generateLatestRatesUrl(): string
    {
        return $this->providerUrl . '/latest?' . http_build_query([
            'access_key' => $this->accessKey,
            'base' => self::BASE_CURRENCY,
        ]);
    }
}
