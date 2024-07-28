<?php

namespace ComissionCalculator\Services;

use ComissionCalculator\Exceptions\InvalidTransactionsFileException;
use ComissionCalculator\Exceptions\InvalidTransactionTypeException;
use ComissionCalculator\Exceptions\MissingTransactionDataKeyException;
use ComissionCalculator\Exceptions\UnprocessableTransactionDataException;
use ComissionCalculator\Structures\TransactionData;

class TransactionsReader
{
    private const REQUIRED_TRANSACTION_KEYS = [
        'bin',
        'amount',
        'currency',
    ];

    /** @var false|resource */
    private $filePointer;

    /**
     * @param string $transactionsFilePath
     * @throws InvalidTransactionsFileException
     */
    public function __construct(string $transactionsFilePath)
    {
        $this->validateTransactionsFilePath($transactionsFilePath);
        $this->filePointer = fopen($transactionsFilePath, 'r');
    }

    /**
     * @return TransactionData|null
     * @throws UnprocessableTransactionDataException
     */
    public function readNextTransaction(): ?TransactionData
    {
        $transactionStringData = fgets($this->filePointer);

        if (!$transactionStringData) {
            return null;
        }

        $transactionArrayData = json_decode($transactionStringData, true);

        if (!is_array($transactionArrayData)) {
            throw new InvalidTransactionTypeException();
        }

        $this->validateTransactionDataStructure($transactionArrayData);

        $transactionData = new TransactionData();
        $transactionData->bin = $transactionArrayData['bin'];
        $transactionData->amount = $transactionArrayData['amount'];
        $transactionData->currency = $transactionArrayData['currency'];

        return $transactionData;
    }

    /**
     * @param string $transactionsFilePath
     * @throws InvalidTransactionsFileException
     */
    private function validateTransactionsFilePath(string $transactionsFilePath): void
    {
        if (!file_exists($transactionsFilePath) || !is_file($transactionsFilePath)) {
            throw new InvalidTransactionsFileException();
        }
    }

    /**
     * @param array $transactionArrayData
     * @throws MissingTransactionDataKeyException
     */
    private function validateTransactionDataStructure(array $transactionArrayData): void
    {
        foreach (self::REQUIRED_TRANSACTION_KEYS as $requiredKey) {
            if (!array_key_exists($requiredKey, $transactionArrayData)) {
                throw new MissingTransactionDataKeyException($requiredKey);
            }
        }
    }

    public function __destruct()
    {
        fclose($this->filePointer);
    }
}
