<?php

use ComissionCalculator\Services\TransactionsReader;

require __DIR__ . '/vendor/autoload.php';

$transactionsFilePath = __DIR__ . '/input.txt';

$transactionsReader = new TransactionsReader($transactionsFilePath);
$transactions = $transactionsReader->getTransactions();
var_dump($transactions);
