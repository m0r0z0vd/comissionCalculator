<?php

namespace ComissionCalculator\Services;

class TransactionsReader
{
    /** @var string */
    private string $transactionsFilePath;

    public function __construct(string $transactionsFilePath)
    {
        $this->transactionsFilePath = $transactionsFilePath;
    }

    /**
     * @return false|string
     */
    public function getTransactions(): false|string
    {
        return file_get_contents($this->transactionsFilePath);
    }
}
