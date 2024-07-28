<?php

namespace CommissionApp\Services;

use CommissionApp\Exceptions\ExchangeRateNotFoundException;
use CommissionApp\Exceptions\InvalidCountryDataException;
use CommissionApp\Exceptions\InvalidUrlException;
use CommissionApp\Structures\TransactionData;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Currency;
use Money\Currencies\ISOCurrencies;

class CommissionCalculator
{
    /** @var CardNumberCountryDataRetriever */
    private CardNumberCountryDataRetriever $countryDataRetriever;

    /** @var array<string, float> */
    private array $exchangeRates;

    /** @var ISOCurrencies */
    private ISOCurrencies $currencies;

    /**
     * @param CardNumberCountryDataRetriever $countryDataRetriever
     * @param array<string, float> $exchangeRates
     */
    public function __construct(
        CardNumberCountryDataRetriever $countryDataRetriever,
        array $exchangeRates
    ) {
        $this->countryDataRetriever = $countryDataRetriever;
        $this->exchangeRates = $exchangeRates;
        $this->currencies = new ISOCurrencies();
    }

    /**
     * @param TransactionData $transaction
     * @return string
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     * @throws ExchangeRateNotFoundException
     */
    public function calculateCommissionInEUR(TransactionData $transaction): string
    {
        $country = $this->countryDataRetriever->getCountryData($transaction->bin);
        $amount = $this->parseAmount($transaction->amount, $transaction->currency);
        $convertedAmount = $this->convertToEur($amount);
        $commission = $this->calculateCommission($convertedAmount, $country->isEU);
        return $this->formatAmount($commission);
    }

    /**
     * @param string $amount
     * @param string $currency
     * @return Money
     */
    private function parseAmount(string $amount, string $currency): Money
    {
        return new Money($amount * 100, new Currency($currency));
    }

    /**
     * @param Money $amount
     * @return Money
     * @throws ExchangeRateNotFoundException
     */
    private function convertToEur(Money $amount): Money
    {
        $currencyCode = $amount->getCurrency()->getCode();

        if ($currencyCode === 'EUR') {
            return $amount;
        }

        if (!array_key_exists($currencyCode, $this->exchangeRates)) {
            throw new ExchangeRateNotFoundException($currencyCode);
        }

        $exchangeRate = $this->exchangeRates[$currencyCode];
        $amountInEur = $amount->multiply($exchangeRate);
        return new Money($amountInEur->getAmount(), new Currency('EUR'));
    }


    /**
     * @param Money $amount
     * @param bool $isEu
     * @return Money
     */
    private function calculateCommission(Money $amount, bool $isEu): Money
    {
        $commissionRate = $isEu ? 0.01 : 0.02;
        $commissionAmount = $amount->multiply($commissionRate);
        return new Money($commissionAmount->getAmount(), new Currency('EUR'));
    }

    /**
     * @param Money $amount
     * @return string
     */
    private function formatAmount(Money $amount): string
    {
        $formatter = new DecimalMoneyFormatter($this->currencies);
        return $formatter->format($amount);
    }
}
