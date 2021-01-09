<?php

namespace bitbuyAT\Bitstamp\Tests;

use bitbuyAT\Bitstamp\Client;
use bitbuyAT\Bitstamp\Exceptions\BitstampApiErrorException;
use bitbuyAT\Bitstamp\Objects\DepositAddress;
use bitbuyAT\Bitstamp\Objects\UserTransaction;
use bitbuyAT\Bitstamp\Objects\UserTransactionsCollection;
use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\TestCase;

class PrivateClientTest extends TestCase
{
    protected $bitstampService;
    protected $ticker;
    protected $orderBook;
    protected $transactions;
    protected $assetPairs;

    protected function setUp(): void
    {
        parent::setUp();
        // instantiate service
        $this->bitstampService = new Client(
            new HttpClient(),
            getenv('BITSTAMP_KEY') ?? null,
            getenv('BITSTAMP_SECRET') ?? null,
            getenv('BITSTAMP_CUSTOMER_ID') ?? null
        );
    }

    public function testClientInstanceCanBeCreated(): void
    {
        $this->assertInstanceOf(Client::class, $this->bitstampService);
    }

    public function testGetAccountBalance(): void
    {
        $accountBalance = $this->bitstampService->getAccountBalance();
        $data = $accountBalance->getData();
        $this->assertArrayHasKey('btc_balance', $data);
        $this->assertArrayHasKey('bch_balance', $data);
        $this->assertArrayHasKey('eth_balance', $data);
        $this->assertArrayHasKey('ltc_balance', $data);
        $this->assertArrayHasKey('eur_balance', $data);
        $this->assertArrayHasKey('usd_balance', $data);
        $this->assertArrayHasKey('xrp_balance', $data);
        $this->assertArrayHasKey('btc_reserved', $data);
        $this->assertArrayHasKey('bch_reserved', $data);
        $this->assertArrayHasKey('eth_reserved', $data);
        $this->assertArrayHasKey('ltc_reserved', $data);
        $this->assertArrayHasKey('eur_reserved', $data);
        $this->assertArrayHasKey('usd_reserved', $data);
        $this->assertArrayHasKey('xrp_reserved', $data);
        $this->assertArrayHasKey('btc_available', $data);
        $this->assertArrayHasKey('bch_available', $data);
        $this->assertArrayHasKey('eth_available', $data);
        $this->assertArrayHasKey('ltc_available', $data);
        $this->assertArrayHasKey('eur_available', $data);
        $this->assertArrayHasKey('usd_available', $data);
        $this->assertArrayHasKey('xrp_available', $data);
        $this->assertArrayHasKey('bchbtc_fee', $data);
        $this->assertArrayHasKey('bcheur_fee', $data);
        $this->assertArrayHasKey('bchusd_fee', $data);
        $this->assertArrayHasKey('btcusd_fee', $data);
        $this->assertArrayHasKey('btceur_fee', $data);
        $this->assertArrayHasKey('ethbtc_fee', $data);
        $this->assertArrayHasKey('etheur_fee', $data);
        $this->assertArrayHasKey('ethusd_fee', $data);
        $this->assertArrayHasKey('ltcbtc_fee', $data);
        $this->assertArrayHasKey('ltceur_fee', $data);
        $this->assertArrayHasKey('ltcusd_fee', $data);
        $this->assertArrayHasKey('eurusd_fee', $data);
        $this->assertArrayHasKey('xrpusd_fee', $data);
        $this->assertArrayHasKey('xrpeur_fee', $data);
        $this->assertArrayHasKey('xrpbtc_fee', $data);

        // test various methods
        $this->assertEquals($accountBalance->eurBalance(), $data['eur_balance']);
        $this->assertEquals($accountBalance->ethReserved(), $data['eth_reserved']);
        $this->assertEquals($accountBalance->btcAvailable(), $data['btc_available']);
        $this->assertEquals($accountBalance->btceurFee(), $data['btceur_fee']);
    }

    public function testGetUserTransactions(): void
    {
        $userTransactions = $this->bitstampService->getUserTransactions('btceur');
        $firstUserTransaction = $userTransactions->first();
        $this->assertInstanceOf(UserTransactionsCollection::class, $userTransactions);
        // only do further tests if the user has transactions
        if ($firstUserTransaction) {
            $data = $firstUserTransaction->getData();
            $this->assertInstanceOf(UserTransaction::class, $firstUserTransaction);
            $this->assertArrayHasKey('datetime', $data);
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('type', $data);
            $this->assertArrayHasKey('usd', $data);
            $this->assertArrayHasKey('eur', $data);
            $this->assertArrayHasKey('btc', $data);
            $this->assertArrayHasKey('btc_eur', $data);
            $this->assertArrayHasKey('fee', $data);
            $this->assertArrayHasKey('order_id', $data);
            $this->assertArrayHasKey('btc', $data);

            // test various methods
            $this->assertEquals($firstUserTransaction->getDatetime(), $data['datetime']);
            $this->assertEquals($firstUserTransaction->eur(), $data['eur']);
            $this->assertEquals($firstUserTransaction->btceurExchangeRate(), $data['btc_eur']);
            $this->assertEquals($firstUserTransaction->getFee(), $data['fee']);
        }
    }

    public function testThrowErrorOnInvalidParamsWhenGettingUserTransactions(): void
    {
        $this->expectException(BitstampApiErrorException::class);
        $this->expectExceptionMessage('Invalid offset.');
        $this->bitstampService->getUserTransactions('btceur', -1);
    }

    public function testItShouldGetAllUserTransactionsIfPairIsEmpty(): void
    {
        $userTransactions = $this->bitstampService->getUserTransactions();
        $this->assertInstanceOf(UserTransactionsCollection::class, $userTransactions);
    }

    public function testGetAddresses(): void
    {
        $ethAddress = $this->bitstampService->getDepositAddress('ETH');
        $this->assertInstanceOf(DepositAddress::class, $ethAddress);
        $this->assertArrayHasKey('address', $ethAddress->getData());

        $paxAddress = $this->bitstampService->getDepositAddress('PAX');
        $this->assertInstanceOf(DepositAddress::class, $paxAddress);
        $this->assertArrayHasKey('address', $paxAddress->getData());

        // they should be the same because PAX are ETH ERC-20 tokens and use the same address for deposit
        $this->assertEquals($paxAddress->getAddress(), $ethAddress->getAddress());
    }

    public function testGetBitcoinAddress(): void
    {
        $btcAddress = $this->bitstampService->getDepositAddress('BTC');
        $this->assertArrayHasKey('address', $btcAddress->getData());
    }
}
