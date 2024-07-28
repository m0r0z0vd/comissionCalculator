<?php

namespace CommissionApp\Tests;

use CommissionApp\Exceptions\InvalidRatesDataException;
use CommissionApp\Exceptions\InvalidRatesDataTypeException;
use CommissionApp\Exceptions\InvalidUrlException;
use CommissionApp\Exceptions\MissingRatesDataKeyException;
use CommissionApp\Services\CurrencyRatesRetriever;
use CommissionApp\Wrappers\FilesystemWrapper;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CurrencyRatesRetrieverTest extends TestCase
{
    private const PROVIDER_URL = 'https://api.example.com';
    private const ACCESS_KEY = 'test_access_key';

    /** @var CurrencyRatesRetriever */
    private CurrencyRatesRetriever $service;

    /** @var string */
    private string $ratesData = '{"rates": {"USD": 1.1, "JPY": 130.0, "GBP": 0.9}}';

    protected function setUp(): void
    {
        parent::setUp();

        $filesystemWrapper = $this->mockFilesystemWrapper();
        $this->service = new CurrencyRatesRetriever(
            $filesystemWrapper,
            self::PROVIDER_URL,
            self::ACCESS_KEY
        );
    }

    /**
     * @throws InvalidRatesDataException
     * @throws InvalidUrlException
     */
    public function testGetLatestRates(): void
    {
        $expectedResult = [
            'USD' => 1.1,
            'JPY' => 130.0,
            'GBP' => 0.9,
        ];
        $actualResult = $this->service->getLatestRates();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @throws InvalidRatesDataException
     * @throws InvalidUrlException
     */
    public function testGetLatestRatesIfNoUrlProvided(): void
    {
        $filesystemWrapper = $this->mockFilesystemWrapper();
        $this->service = new CurrencyRatesRetriever(
            $filesystemWrapper,
            '',
            self::ACCESS_KEY
        );

        $this->expectException(InvalidUrlException::class);
        $this->service->getLatestRates();
    }

    /**
     * @throws InvalidRatesDataException
     * @throws InvalidUrlException
     */
    public function testGetLatestRatesIfInvalidUrl(): void
    {
        $filesystemWrapper = $this->mockFilesystemWrapper();
        $this->service = new CurrencyRatesRetriever(
            $filesystemWrapper,
            '1234',
            self::ACCESS_KEY
        );

        $this->expectException(InvalidUrlException::class);
        $this->service->getLatestRates();
    }

    /**
     * @throws InvalidRatesDataException
     * @throws InvalidUrlException
     */
    public function testGetLatestRatesIfNoResponse(): void
    {
        $this->ratesData = '';

        $this->expectException(InvalidRatesDataTypeException::class);
        $this->service->getLatestRates();
    }

    /**
     * @throws InvalidRatesDataException
     * @throws InvalidUrlException
     */
    public function testGetLatestRatesIfInvalidResponse(): void
    {
        $this->ratesData = '1234';

        $this->expectException(InvalidRatesDataTypeException::class);
        $this->service->getLatestRates();
    }

    /**
     * @throws InvalidRatesDataException
     * @throws InvalidUrlException
     */
    public function testGetLatestRatesIfMissingRatesKey(): void
    {
        $this->ratesData = '{"base": "EUR"}';

        $this->expectException(MissingRatesDataKeyException::class);
        $this->service->getLatestRates();
    }

    /**
     * @return FilesystemWrapper|MockInterface
     */
    private function mockFilesystemWrapper(): FilesystemWrapper|MockInterface
    {
        $filesystemWrapper = Mockery::mock(FilesystemWrapper::class);
        $filesystemWrapper->shouldReceive('fileGetContents')->andReturnUsing(function () {
            return $this->ratesData;
        });

        return $filesystemWrapper;
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
