<?php

namespace bitbuyAT\Bitstamp\Contracts;

use bitbuyAT\Bitstamp\Exceptions\BitstampApiErrorException;
use bitbuyAT\Bitstamp\Objects\Balance;
use bitbuyAT\Bitstamp\Objects\DepositAddress;
use bitbuyAT\Bitstamp\Objects\OrderBook;
use bitbuyAT\Bitstamp\Objects\PairsCollection;
use bitbuyAT\Bitstamp\Objects\Ticker;
use bitbuyAT\Bitstamp\Objects\TransactionsCollection;
use bitbuyAT\Bitstamp\Objects\UserTransactionsCollection;

interface Client
{
    /**
     * Get ticker information.
     *
     * @throws BitstampApiErrorException
     */
    public function getTicker(string $pair): Ticker;

    /**
     * Get hourly ticker information.
     *
     * @throws BitstampApiErrorException
     */
    public function getHourlyTicker(string $pair): Ticker;

    /**
     * Get order book.
     *
     * @param int $group optional group
     *                   0: orders are not grouped at same price
     *                   1: orders are grouped at same price - default
     *                   2: orders with their order ids are not grouped at same price
     *
     * @throws BitstampApiErrorException
     */
    public function getOrderBook(string $pair, ?int $group = 1): OrderBook;

    /**
     * Get current transactions.
     *
     * @param string $time The time interval from which we want the transactions to be returned. Possible values are minute, hour (default) or day.
     *
     * @return TransactionsCollection|Transaction[]
     *
     * @throws BitstampApiErrorException
     */
    public function getTransactions(string $pair, ?string $time = 'hour'): TransactionsCollection;

    /**
     * Get tradable asset pairs.
     *
     * @return PairsCollection|Pair[]
     *
     * @throws BitstampApiErrorException
     */
    public function getAssetPairs(): PairsCollection;

    /**
     * Get account balance.
     *
     * @throws BitstampApiErrorException
     */
    public function getAccountBalance(): Balance;

    /**
     * Get user transactions.
     *
     * @param string [$pair=null] - Pair to filter for, if left empty there will be queried for all pairs (default: null)
     * @param int [$offset=0] - Skip that many transactions before returning results (default: 0)
     * @param int [$limit=100] - Limit result to that many transactions (default: 100; maximum: 1000)
     * @param string [$sort='desc'] - Sorting by date and time: asc - ascending; desc - descending (default: desc)
     * @param int [$sinceTimestamp] - Show only transactions from unix timestamp (for max 30 days old)
     *
     * @return UserTransactionsCollection|Transaction[]
     *
     * @throws BitstampApiErrorException
     */
    public function getUserTransactions(?string $pair = null, ?int $offset = 0, ?int $limit = 100, ?string $sort = 'desc', ?int $sinceTimestamp = null): UserTransactionsCollection;

    /**
     * Gets deposit address for given asset.
     *
     * @param string $assetCode Asset code of the deposit address to be displayed (e.g. BTC, ETH, XRP).
     *
     * @throws BitstampApiErrorException
     */
    public function getDepositAddress(string $assetCode): DepositAddress;

    /**
     * Make public request request
     * Currently only get request.
     *
     * @param array $parameters
     *
     * @throws BitstampApiErrorException
     */
    public function publicRequest(string $method, string $path = '', $parameters = [], string $version = 'v2'): array;

    /**
     * Make private request request
     * Currently only post request.
     *
     * @throws BitstampApiErrorException
     */
    public function privateRequest(string $method, array $parameters = [], string $version = 'v2'): array;
}
