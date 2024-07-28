# Commission Calculator App

## To install
- clone the repo
- create `.env` file in the project root
- set the following env vars: `BIN_LIST_PROVIDER_URL` (can be used from `.env.example`), `EXCHANGE_RATES_PROVIDER_URL`(can be used from `.env.example`) and `EXCHANGER_ACCESS_KEY` (personal access key for https://api.exchangeratesapi.io)
- run `composer install`

## To test manually
- run `php app.php input.txt`

## To execute the automated tests
- run `./vendor/bin/phpunit ./tests`
