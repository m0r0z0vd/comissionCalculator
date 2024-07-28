<?php

use CommissionApp\Services\CardNumberCountryDataRetriever;
use CommissionApp\Services\CommissionCalculator;
use CommissionApp\Services\TransactionsReader;
use CommissionApp\Wrappers\FilesystemWrapper;
use CommissionApp\Wrappers\GeographerWrapper;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$fileSystemWrapper = new FilesystemWrapper();
$geographerWrapper = new GeographerWrapper();

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$binListProviderUrl = (string)$_ENV['BIN_LIST_PROVIDER_URL'];

$transactionsFilePath = __DIR__ . '/' . (string)$argv[1];

$exchangeRates = [
    'USD' => 0.85,
    'JPY' => 0.0078,
    'GBP' => 1.17,
    'EUR' => 1.0,
];

$transactionsReader = new TransactionsReader($fileSystemWrapper, $transactionsFilePath);
$countryDataRetriever = new CardNumberCountryDataRetriever($fileSystemWrapper, $geographerWrapper, $binListProviderUrl);
$commissionCalculator = new CommissionCalculator($countryDataRetriever, $exchangeRates);

while ($transactionData = $transactionsReader->readNextTransaction()) {
    var_dump($transactionData);
    $commission = $commissionCalculator->calculate($transactionData);
    var_dump($commission);
}
