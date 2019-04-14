<?php

namespace bitbuyAT\Bitstamp\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as HttpClient;
use bitbuyAT\Bitstamp\Client;
use bitbuyAT\Bitstamp\Objects\UserTransaction;
use bitbuyAT\Bitstamp\Objects\UserTransactionsCollection;
use bitbuyAT\Bitstamp\Exceptions\BitstampApiErrorException;

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

    public function test_client_instance_can_be_created(): void
    {
        $this->assertInstanceOf(Client::class, $this->bitstampService);
    }

    public function test_get_account_balance(): void
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

    public function test_get_user_transactions(): void
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

    public function test_throw_error_on_invalid_params_when_getting_user_transactions(): void
    {
        $this->expectException(BitstampApiErrorException::class);
        $this->expectExceptionMessage('Invalid offset.');
        $this->bitstampService->getUserTransactions('btceur', -1);
    }
}
