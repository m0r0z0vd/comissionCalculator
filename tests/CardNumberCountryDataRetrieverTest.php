<?php

namespace CommissionApp\Tests;

use CommissionApp\Exceptions\InvalidCountryDataException;
use CommissionApp\Exceptions\InvalidCountryDataTypeException;
use CommissionApp\Exceptions\InvalidUrlException;
use CommissionApp\Exceptions\MissingCountryDataResponseKeyException;
use CommissionApp\Services\CardNumberCountryDataRetriever;
use CommissionApp\Structures\CountryData;
use CommissionApp\Wrappers\FilesystemWrapper;
use CommissionApp\Wrappers\GeographerWrapper;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CardNumberCountryDataRetrieverTest extends TestCase
{
    private const BIN = '4242';

    /** @var string */
    private string $binListProviderUrl = 'https://lookup.example.net';

    /** @var string */
    private string $countryData = '{"country": {"alpha2": "UA"}}';

    /**
     * @var string[]
     */
    private array $countryCodes = [
        'UA',
        'GB',
        'CH',
    ];

    /** @var CardNumberCountryDataRetriever */
    private CardNumberCountryDataRetriever $service;

    protected function setUp(): void
    {
        parent::setUp();

        $fileSystemWrapper = $this->mockFilesystemWrapper();
        $geographerWrapper = $this->mockGeographerWrapper();
        $this->service = new CardNumberCountryDataRetriever(
            $fileSystemWrapper,
            $geographerWrapper,
            $this->binListProviderUrl
        );
    }

    /**
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testGetCountryData(): void
    {
        $expectedResult = new CountryData();
        $expectedResult->countryCode = 'UA';
        $expectedResult->isEU = true;
        $actualResult = $this->service->getCountryData(self::BIN);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testGetCountryIfNoUrlProvided(): void
    {
        $fileSystemWrapper = $this->mockFilesystemWrapper();
        $geographerWrapper = $this->mockGeographerWrapper();
        $this->service = new CardNumberCountryDataRetriever(
            $fileSystemWrapper,
            $geographerWrapper,
            ''
        );

        $this->expectException(InvalidUrlException::class);
        $this->service->getCountryData(self::BIN);
    }

    /**
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testGetCountryIfInvalidUrl(): void
    {
        $fileSystemWrapper = $this->mockFilesystemWrapper();
        $geographerWrapper = $this->mockGeographerWrapper();
        $this->service = new CardNumberCountryDataRetriever(
            $fileSystemWrapper,
            $geographerWrapper,
            '1234'
        );

        $this->expectException(InvalidUrlException::class);
        $this->service->getCountryData(self::BIN);
    }

    /**
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testGetCountryIfNoResponse(): void
    {
        $this->countryData = '';

        $this->expectException(InvalidCountryDataTypeException::class);
        $this->service->getCountryData(self::BIN);
    }

    /**
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testGetCountryIfInvalidResponse(): void
    {
        $this->countryData = '1234';

        $this->expectException(InvalidCountryDataTypeException::class);
        $this->service->getCountryData(self::BIN);
    }

    /**
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testGetCountryIfMissingCountryResponseKey(): void
    {
        $this->countryData = '{"city": "Kiev"}';

        $this->expectException(MissingCountryDataResponseKeyException::class);
        $this->service->getCountryData(self::BIN);
    }

    /**
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testGetCountryIfMissingAlphaResponseKey(): void
    {
        $this->countryData = '{"country": {"capital": "Kiev"}}';

        $this->expectException(MissingCountryDataResponseKeyException::class);
        $this->service->getCountryData(self::BIN);
    }

    /**
     * @return FilesystemWrapper|MockInterface
     */
    private function mockFilesystemWrapper(): FilesystemWrapper|MockInterface
    {
        $filesystemWrapper = Mockery::mock(FilesystemWrapper::class);
        $filesystemWrapper->shouldReceive('fileGetContents')->andReturnUsing(function () {
            return $this->countryData;
        });

        return $filesystemWrapper;
    }

    /**
     * @return GeographerWrapper|MockInterface
     */
    private function mockGeographerWrapper(): GeographerWrapper|MockInterface
    {
        $geographerWrapper = Mockery::mock(GeographerWrapper::class);
        $geographerWrapper->shouldReceive('getEuropeCountryCodes')->andReturnUsing(function () {
            return $this->countryCodes;
        });

        return $geographerWrapper;
    }
}
