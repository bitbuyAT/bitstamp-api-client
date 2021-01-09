<?php

namespace bitbuyAT\Bitstamp\Tests;

use bitbuyAT\Bitstamp\Client;
use bitbuyAT\Bitstamp\Exceptions\BitstampApiErrorException;
use bitbuyAT\Bitstamp\Objects\OrderBook;
use bitbuyAT\Bitstamp\Objects\Pair;
use bitbuyAT\Bitstamp\Objects\PairsCollection;
use bitbuyAT\Bitstamp\Objects\Ticker;
use bitbuyAT\Bitstamp\Objects\Transaction;
use bitbuyAT\Bitstamp\Objects\TransactionsCollection;
use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\TestCase;

class PublicClientTest extends TestCase
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
        $this->bitstampService = new Client(new HttpClient());
        // get ticker
        $this->ticker = $this->bitstampService->getTicker('btcusd');
        // get order book
        $this->orderBook = $this->bitstampService->getOrderBook('btcusd');
        // get transactions
        $this->transactions = $this->bitstampService->getTransactions('btcusd');
        // get transactions
        $this->assetPairs = $this->bitstampService->getAssetPairs();
    }

    public function testClientInstanceCanBeCreated(): void
    {
        $this->assertInstanceOf(Client::class, $this->bitstampService);
    }

    public function testTickerInstanceCanBeCreatedFromGetTicker(): void
    {
        $this->assertInstanceOf(Ticker::class, $this->ticker);
    }

    public function testGetDataOfTickerReturnsArrayWithAllKeys(): void
    {
        $data = $this->ticker->getData();
        $this->assertArrayHasKey('last', $data);
        $this->assertArrayHasKey('high', $data);
        $this->assertArrayHasKey('low', $data);
        $this->assertArrayHasKey('vwap', $data);
        $this->assertArrayHasKey('volume', $data);
        $this->assertArrayHasKey('bid', $data);
        $this->assertArrayHasKey('ask', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('open', $data);
    }

    public function testGetBidAndAskPriceOfTicker(): void
    {
        $data = $this->ticker->getData();
        $bidPrice = $this->ticker->bidPrice();
        $askPrice = $this->ticker->askPrice();

        $this->assertIsFloat($bidPrice);
        $this->assertEquals($data['bid'], $bidPrice);
        $this->assertIsFloat($askPrice);
        $this->assertEquals($data['ask'], $askPrice);
    }

    public function testThrowErrorIfPairDoesNotExist(): void
    {
        $this->expectException(BitstampApiErrorException::class);
        $this->bitstampService->getTicker('abcdef');
    }

    public function testTickerInstanceCanBeCreatedFromGetHourlyTicker(): void
    {
        $hourlyTicker = $this->bitstampService->getHourlyTicker('btcusd');
        $this->assertInstanceOf(Ticker::class, $hourlyTicker);
    }

    public function testOrderBookInstanceCanBeCreatedFromGetOrderBook(): void
    {
        $this->assertInstanceOf(OrderBook::class, $this->orderBook);
    }

    public function testGetDataOfOrderBookReturnsArrayWithAllKeys(): void
    {
        $data = $this->orderBook->getData();
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('bids', $data);
        $this->assertArrayHasKey('asks', $data);
    }

    public function testGetBidsAndAsksOrderBook(): void
    {
        $data = $this->orderBook->getData();
        $bidPrices = $this->orderBook->getBids();
        $askPrices = $this->orderBook->getAsks();

        $this->assertIsArray($bidPrices);
        $this->assertEquals($data['bids'], $bidPrices);
        $this->assertIsArray($askPrices);
        $this->assertEquals($data['asks'], $askPrices);
    }

    public function testTransactionsCollectionInstanceCanBeCreatedFromGetTransactions(): void
    {
        $this->assertInstanceOf(TransactionsCollection::class, $this->transactions);
    }

    public function testFirstOfTransactionsReturnsTransactionObject(): void
    {
        $firstTransaction = $this->transactions->first();
        $data = $firstTransaction->getData();
        $this->assertInstanceOf(Transaction::class, $firstTransaction);
        $this->assertArrayHasKey('date', $data);
        $this->assertArrayHasKey('tid', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('type', $data);
    }

    public function testPairsCollectionInstanceCanBeCreatedFromGetAssetPairs(): void
    {
        $this->assertInstanceOf(PairsCollection::class, $this->assetPairs);
    }

    public function testFirstOfPairsReturnsPairObject(): void
    {
        $firstPair = $this->assetPairs->first();
        $data = $firstPair->getData();
        $this->assertInstanceOf(Pair::class, $firstPair);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('url_symbol', $data);
        $this->assertArrayHasKey('base_decimals', $data);
        $this->assertArrayHasKey('counter_decimals', $data);
        $this->assertArrayHasKey('minimum_order', $data);
        $this->assertArrayHasKey('trading', $data);
        $this->assertArrayHasKey('description', $data);
    }
}
