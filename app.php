<?php

use CommissionApp\Services\CardNumberCountryDataRetriever;
use CommissionApp\Services\CommissionCalculator;
use CommissionApp\Services\CurrencyRatesRetriever;
use CommissionApp\Services\TransactionsReader;
use CommissionApp\Wrappers\FilesystemWrapper;
use CommissionApp\Wrappers\GeographerWrapper;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$transactionsFilePath = __DIR__ . '/' . (string)$argv[1];

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$binListProviderUrl = (string)$_ENV['BIN_LIST_PROVIDER_URL'];
$exchangeRatesProviderUrl = (string)$_ENV['EXCHANGE_RATES_PROVIDER_URL'];
$accessKey = (string)$_ENV['EXCHANGER_ACCESS_KEY'];

$fileSystemWrapper = new FilesystemWrapper();
$geographerWrapper = new GeographerWrapper();

$transactionsReader = new TransactionsReader($fileSystemWrapper, $transactionsFilePath);
$countryDataRetriever = new CardNumberCountryDataRetriever($fileSystemWrapper, $geographerWrapper, $binListProviderUrl);
$currencyRatesRetriever = new CurrencyRatesRetriever($fileSystemWrapper, $exchangeRatesProviderUrl, $accessKey);
$exchangeRates = $currencyRatesRetriever->getLatestRates();
$commissionCalculator = new CommissionCalculator($countryDataRetriever, $exchangeRates);

$commissionResults = [];

while ($transactionData = $transactionsReader->readNextTransaction()) {
    $transactionData->commissionInEUR = $commissionCalculator->calculateCommissionInEUR($transactionData);
    $commissionResults[] = $transactionData;
}

echo "Transactions data with commissions calculated: " . PHP_EOL;
print_r($commissionResults);
