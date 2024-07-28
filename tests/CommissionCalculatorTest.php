<?php

namespace CommissionApp\Tests;

use CommissionApp\Exceptions\ExchangeRateNotFoundException;
use CommissionApp\Exceptions\InvalidCountryDataException;
use CommissionApp\Exceptions\InvalidUrlException;
use CommissionApp\Services\CardNumberCountryDataRetriever;
use CommissionApp\Structures\CountryData;
use CommissionApp\Structures\TransactionData;
use CommissionApp\Services\CommissionCalculator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    private const EXCHANGE_RATES = [
        'USD' => 0.85,
        'GBP' => 1.15,
    ];

    /** @var CommissionCalculator */
    private CommissionCalculator $calculator;

    /** @var CountryData */
    private CountryData $countryData;

    /** @var bool */
    private bool $countryDataException = false;

    protected function setUp(): void
    {
        parent::setUp();

        $countryData = new CountryData();
        $countryData->countryCode = 'UA';
        $countryData->isEU = true;

        $this->countryData = $countryData;

        $cardNumberCountryDataRetriever = $this->mockCardNumberCountryDataRetriever();

        $this->calculator = new CommissionCalculator(
            $cardNumberCountryDataRetriever,
            self::EXCHANGE_RATES
        );
    }

    /**
     * @throws ExchangeRateNotFoundException
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testCalculateCommissionInEURWithValidData(): void
    {
        $this->countryData->isEU = false;

        $transaction = new TransactionData();
        $transaction->bin = '123456';
        $transaction->amount = '100';
        $transaction->currency = 'USD';

        $result = $this->calculator->calculateCommissionInEUR($transaction);

        $this->assertSame('1.70', $result);
    }

    /**
     * @throws ExchangeRateNotFoundException
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testCalculateCommissionInEURWithInvalidCountryData(): void
    {
        $this->countryDataException = true;

        $this->expectException(InvalidCountryDataException::class);

        $transaction = new TransactionData();
        $transaction->bin = '123456';
        $transaction->amount = '100';
        $transaction->currency = 'USD';

        $this->calculator->calculateCommissionInEUR($transaction);
    }

    /**
     * @throws ExchangeRateNotFoundException
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testCalculateCommissionInEURWithMissingExchangeRate(): void
    {
        $this->expectException(ExchangeRateNotFoundException::class);

        $transaction = new TransactionData();
        $transaction->bin = '123456';
        $transaction->amount = '100';
        $transaction->currency = 'JPY';

        $this->calculator->calculateCommissionInEUR($transaction);
    }

    /**
     * @throws ExchangeRateNotFoundException
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testCalculateCommissionInEURWithEURCurrency(): void
    {
        $transaction = new TransactionData();
        $transaction->bin = '123456';
        $transaction->amount = '100';
        $transaction->currency = 'EUR';

        $result = $this->calculator->calculateCommissionInEUR($transaction);

        $this->assertSame('1.00', $result);
    }

    /**
     * @throws ExchangeRateNotFoundException
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testCalculateCommissionInEURWithEUCountry(): void
    {
        $transaction = new TransactionData();
        $transaction->bin = '123456';
        $transaction->amount = '200';
        $transaction->currency = 'GBP';

        $result = $this->calculator->calculateCommissionInEUR($transaction);

        $this->assertSame('2.30', $result);
    }

    /**
     * @throws ExchangeRateNotFoundException
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testCalculateCommissionInEURWithZeroAmount(): void
    {
        $transaction = new TransactionData();
        $transaction->bin = '123456';
        $transaction->amount = '0';
        $transaction->currency = 'USD';

        $this->mockCardNumberCountryDataRetriever()->shouldReceive('getCountryData')
            ->with('123456')
            ->andReturn($this->countryData);

        $result = $this->calculator->calculateCommissionInEUR($transaction);

        $this->assertSame('0.00', $result);
    }

    /**
     * @throws ExchangeRateNotFoundException
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testCalculateCommissionInEURWithNegativeAmount(): void
    {
        $this->countryData->isEU = false;

        $transaction = new TransactionData();
        $transaction->bin = '123456';
        $transaction->amount = '-100';
        $transaction->currency = 'USD';

        $this->mockCardNumberCountryDataRetriever()->shouldReceive('getCountryData')
            ->with('123456')
            ->andReturn($this->countryData);

        $result = $this->calculator->calculateCommissionInEUR($transaction);

        $this->assertSame('-1.70', $result);
    }

    /**
     * @throws ExchangeRateNotFoundException
     * @throws InvalidCountryDataException
     * @throws InvalidUrlException
     */
    public function testCalculateCommissionInEURWithDecimalAmount(): void
    {
        $this->countryData->isEU = false;

        $transaction = new TransactionData();
        $transaction->bin = '123456';
        $transaction->amount = '99.99';
        $transaction->currency = 'USD';

        $result = $this->calculator->calculateCommissionInEUR($transaction);

        $this->assertSame('1.70', $result);
    }

    /**
     * @return MockInterface|CardNumberCountryDataRetriever
     */
    private function mockCardNumberCountryDataRetriever(): MockInterface|CardNumberCountryDataRetriever
    {
        $cardNumberCountryDataRetriever = Mockery::mock(CardNumberCountryDataRetriever::class);

        $cardNumberCountryDataRetriever->shouldReceive('getCountryData')->andReturnUsing(function () {
            if ($this->countryDataException) {
                throw new InvalidCountryDataException();
            }

            return $this->countryData;
        });

        return $cardNumberCountryDataRetriever;
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
