<?php

namespace CommissionApp\Tests;

use CommissionApp\Exceptions\InvalidTransactionsFileException;
use CommissionApp\Exceptions\InvalidTransactionTypeException;
use CommissionApp\Exceptions\MissingTransactionDataKeyException;
use CommissionApp\Exceptions\UnprocessableTransactionDataException;
use CommissionApp\Services\TransactionsReader;
use CommissionApp\Structures\TransactionData;
use CommissionApp\Wrappers\FilesystemWrapper;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class TransactionsReaderTest extends TestCase
{
    private const TRANSACTIONS_FILE_PATH = 'transactions.txt';

    /** @var TransactionsReader */
    private TransactionsReader $service;

    /** @var string */
    private string $transactionData = '{"bin": "45717360", "amount": "100.00", "currency": "EUR"}';

    /** @var bool */
    private bool $fileExists = true;

    /** @var bool */
    private bool $isFile = true;

    private bool $fileOpened = true;

    /**
     * @throws InvalidTransactionsFileException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $filesystemWrapper = $this->mockFilesystemWrapper();
        $this->service = new TransactionsReader(
            $filesystemWrapper,
            self::TRANSACTIONS_FILE_PATH
        );
    }

    /**
     * @throws UnprocessableTransactionDataException
     */
    public function testReadNextTransaction(): void
    {
        $expectedResult = new TransactionData();
        $expectedResult->bin = '45717360';
        $expectedResult->amount = '100.00';
        $expectedResult->currency = 'EUR';

        $actualResult = $this->service->readNextTransaction();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @throws UnprocessableTransactionDataException
     */
    public function testReadNextTransactionIfNoMoreTransactions(): void
    {
        $this->transactionData = '';

        $actualResult = $this->service->readNextTransaction();
        $this->assertNull($actualResult);
    }

    /**
     * @throws UnprocessableTransactionDataException
     */
    public function testReadNextTransactionIfInvalidTransactionType(): void
    {
        $this->transactionData = 'invalid json';

        $this->expectException(InvalidTransactionTypeException::class);
        $this->service->readNextTransaction();
    }

    /**
     * @throws UnprocessableTransactionDataException
     */
    public function testReadNextTransactionIfMissingRequiredKey(): void
    {
        $this->transactionData = '{"bin": "45717360", "amount": "100.00"}';

        $this->expectException(MissingTransactionDataKeyException::class);
        $this->service->readNextTransaction();
    }

    public function testReadNextTransactionIfFileDoesNotExist(): void
    {
        $this->fileExists = false;

        $this->expectException(InvalidTransactionsFileException::class);

        $filesystemWrapper = $this->mockFilesystemWrapper();
        $this->service = new TransactionsReader(
            $filesystemWrapper,
            self::TRANSACTIONS_FILE_PATH
        );
    }

    public function testReadNextTransactionIfItIsNotFile(): void
    {
        $this->isFile = false;

        $this->expectException(InvalidTransactionsFileException::class);

        $filesystemWrapper = $this->mockFilesystemWrapper();
        $this->service = new TransactionsReader(
            $filesystemWrapper,
            self::TRANSACTIONS_FILE_PATH
        );
    }

    public function testReadNextTransactionIfNoFilePointer(): void
    {
        $this->fileOpened = false;

        $this->expectException(InvalidTransactionsFileException::class);

        $filesystemWrapper = $this->mockFilesystemWrapper();
        $this->service = new TransactionsReader(
            $filesystemWrapper,
            self::TRANSACTIONS_FILE_PATH
        );
    }

    /**
     * @return FilesystemWrapper|MockInterface
     */
    private function mockFilesystemWrapper(): FilesystemWrapper|MockInterface
    {
        $filesystemWrapper = Mockery::mock(FilesystemWrapper::class);
        $filesystemWrapper->shouldReceive('fileExists')->andReturn($this->fileExists);
        $filesystemWrapper->shouldReceive('isFile')->andReturn($this->isFile);
        $filesystemWrapper->shouldReceive('fileOpen')->andReturn($this->fileOpened);
        $filesystemWrapper->shouldReceive('fileGetString')->andReturnUsing(function () {
            return $this->transactionData;
        });
        $filesystemWrapper->shouldReceive('fileClose')->andReturn(true);

        return $filesystemWrapper;
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
