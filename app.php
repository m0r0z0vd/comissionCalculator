<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$transactionsFilePath = __DIR__ . '/input.txt';

var_dump($_ENV['BIN_LIST_PROVIDER_URL']);
