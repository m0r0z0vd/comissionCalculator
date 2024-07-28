<?php

namespace CommissionApp\Services;

use CommissionApp\Exceptions\InvalidTransactionsFileException;
use CommissionApp\Exceptions\InvalidTransactionTypeException;
use CommissionApp\Exceptions\MissingTransactionDataKeyException;
use CommissionApp\Exceptions\UnprocessableTransactionDataException;
use CommissionApp\Structures\TransactionData;
use CommissionApp\Wrappers\FilesystemWrapper;

class TransactionsReader
{
    private const REQUIRED_TRANSACTION_KEYS = [
        'bin',
        'amount',
        'currency',
    ];

    /** @var FilesystemWrapper */
    private FilesystemWrapper $filesystem;

    /** @var false|resource */
    private $filePointer;

    /**
     * @param FilesystemWrapper $filesystem
     * @param string $transactionsFilePath
     * @throws InvalidTransactionsFileException
     */
    public function __construct(FilesystemWrapper $filesystem, string $transactionsFilePath)
    {
        $this->filesystem = $filesystem;

        $this->validateTransactionsFilePath($transactionsFilePath);
        $this->filePointer = $this->filesystem->fileOpen($transactionsFilePath, 'r');
    }

    /**
     * @return TransactionData|null
     * @throws UnprocessableTransactionDataException
     */
    public function readNextTransaction(): ?TransactionData
    {
        $transactionStringData = $this->filesystem->fileGetString($this->filePointer);

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
        if (
            !$this->filesystem->fileExists($transactionsFilePath)
            || !$this->filesystem->isFile($transactionsFilePath)
        ) {
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
        $this->filesystem->fileClose($this->filePointer);
    }
}
