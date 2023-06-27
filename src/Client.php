<?php

namespace bitbuyAT\Bitstamp;

use bitbuyAT\Bitstamp\Contracts\Client as ClientContract;
use bitbuyAT\Bitstamp\Exceptions\BitstampApiErrorException;
use bitbuyAT\Bitstamp\Objects\Balance;
use bitbuyAT\Bitstamp\Objects\DepositAddress;
use bitbuyAT\Bitstamp\Objects\OrderBook;
use bitbuyAT\Bitstamp\Objects\Pair;
use bitbuyAT\Bitstamp\Objects\PairsCollection;
use bitbuyAT\Bitstamp\Objects\Ticker;
use bitbuyAT\Bitstamp\Objects\Transaction;
use bitbuyAT\Bitstamp\Objects\TransactionsCollection;
use bitbuyAT\Bitstamp\Objects\UserTransaction;
use bitbuyAT\Bitstamp\Objects\UserTransactionsCollection;
use GuzzleHttp\ClientInterface as HttpClient;

class Client implements ClientContract
{
    public const API_URL = 'https://www.bitstamp.net/api';

    /**
     * API key.
     *
     * @var string
     */
    protected $key;

    /**
     * API secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * API secret.
     *
     * @var string
     */
    protected $customerId;

    /**
     * Nonce.
     *
     * @var string
     */
    protected $nonce;

    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @param string $key        API key
     * @param string $secret     API secret
     * @param string $customerId customer id (can be found in account balance)
     */
    public function __construct(HttpClient $client, ?string $key = '', ?string $secret = '', ?string $customerId = '')
    {
        $this->client = $client;
        $this->key = $key;
        $this->secret = $secret;
        $this->customerId = $customerId;
    }

    /**
     * Get ticker information.
     *
     * @throws BitstampApiErrorException
     */
    public function getTicker(string $pair): Ticker
    {
        $result = $this->publicRequest('ticker', $pair);

        return new Ticker($result);
    }

    /**
     * Get hourly ticker information.
     *
     * @throws BitstampApiErrorException
     */
    public function getHourlyTicker(string $pair): Ticker
    {
        $result = $this->publicRequest('ticker_hour', $pair);

        return new Ticker($result);
    }

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
    public function getOrderBook(string $pair, ?int $group = 1): OrderBook
    {
        $result = $this->publicRequest('order_book', $pair, ['group' => $group]);

        return new OrderBook($result);
    }

    /**
     * Get current transactions.
     *
     * @param string $time The time interval from which we want the transactions to be returned.
     *                     Possible values are minute, hour (default) or day.
     *
     * @return TransactionsCollection|Transaction[]
     *
     * @throws BitstampApiErrorException
     */
    public function getTransactions(string $pair, ?string $time = 'hour'): TransactionsCollection
    {
        $result = $this->publicRequest('transactions', $pair, ['time' => $time]);

        return (new TransactionsCollection($result))->map(function ($data) {
            return new Transaction($data);
        });
    }

    /**
     * Get tradable asset pairs.
     *
     * @return PairsCollection|Pair[]
     *
     * @throws BitstampApiErrorException
     */
    public function getAssetPairs(): PairsCollection
    {
        $result = $this->publicRequest('trading-pairs-info');

        return (new PairsCollection($result))->map(function ($data) {
            return new Pair($data);
        });
    }

    /**
     * Get account balance.
     *
     * @throws BitstampApiErrorException
     */
    public function getAccountBalance(): Balance
    {
        $result = $this->privateRequest('balance', []);

        return new Balance($result);
    }

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
    public function getUserTransactions(string $pair = null, ?int $offset = 0, ?int $limit = 100, ?string $sort = 'desc', int $sinceTimestamp = null): UserTransactionsCollection
    {
        $params = [
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort,
            'sinceTimestamp' => $sinceTimestamp,
        ];

        $result = $this->privateRequest('user_transactions/'.$pair, $params);

        if (isset($result['status']) && $result['status'] === 'error') {
            throw new BitstampApiErrorException($result['reason']);
        }

        return (new UserTransactionsCollection($result))->map(function ($data) {
            return new UserTransaction($data);
        });
    }

    /**
     * Gets deposit address for given asset.
     *
     * @param string $assetCode Asset code of the deposit address to be displayed (e.g. BTC, ETH, XRP).
     *
     * @throws BitstampApiErrorException
     */
    public function getDepositAddress(string $assetCode): DepositAddress
    {
        return new DepositAddress($this->privateRequest(strtolower($assetCode).'_address'));
    }

    /**
     * Make public request request
     * Currently only get request.
     *
     * @param array $parameters
     *
     * @throws BitstampApiErrorException
     */
    public function publicRequest(string $method, string $path = '', $parameters = [], string $version = 'v2'): array
    {
        $headers = ['User-Agent' => 'Bitstamp PHP API Agent'];

        try {
            $response = $this->client->get($this->buildUrl($method, $version).'/'.$path, [
                'headers' => $headers,
                'query' => $parameters,
            ]);
        } catch (\Exception $exception) {
            if ($exception->getCode() === 404) {
                throw new BitstampApiErrorException('Endpoint not found: ('.$this->buildUrl($method, $version).'/'.$path.')');
            } else {
                throw new BitstampApiErrorException($exception->getMessage());
            }
        }

        return $this->decodeResult(
            $response->getBody()->getContents()
        );
    }

    /**
     * Make private request request
     * Currently only post request.
     *
     * @throws BitstampApiErrorException
     */
    public function privateRequest(string $method, array $parameters = [], string $version = 'v2'): array
    {
        $headers = ['User-Agent' => 'Bitstamp PHP API Agent'];

        $parameters['nonce'] = $this->generateNonce();
        $parameters['key'] = $this->key;
        $parameters['signature'] = $this->generateSign();

        try {
            $response = $this->client->post($this->buildUrl($method, $version).'/', [
                'headers' => $headers,
                'form_params' => $parameters,
                'verify' => true,
            ]);
        } catch (\Exception $exception) {
            if ($exception->getCode() === 404) {
                throw new BitstampApiErrorException('Endpoint not found: ('.$this->buildUrl($method, $version).')');
            } else {
                throw new BitstampApiErrorException($exception);
            }
        }

        $responseContent = $response->getBody()->getContents();

        // According to docs v1 bitcoin_deposit_address endpoint should return json, but instead returns string
        // the following code will fix that
        if ($method === 'bitcoin_deposit_address' && $version === '') {
            $responseContent = '{"address": '.$responseContent.'}';
        }

        return $this->decodeResult($responseContent);
    }

    /**
     * Build url.
     */
    protected function buildUrl(string $method, string $version): string
    {
        return static::API_URL.$this->buildPath($method, $version);
    }

    /**
     * Build path.
     */
    protected function buildPath(string $method, string $version): string
    {
        return empty($version) ? '/'.$method : '/'.$version.'/'.$method;
    }

    /**
     * Compute bitstamp signature
     * message = nonce + customer_id + api_key
     * signature = hmac.new(
     *   API_SECRET,
     *   msg=message,
     *   digestmod=hashlib.sha256
     * ).hexdigest().upper().
     */
    public function generateSign(): string
    {
        $message = $this->nonce.$this->customerId.$this->key;

        return strtoupper(hash_hmac('sha256', $message, $this->secret));
    }

    /**
     * Generate a 64 bit nonce using a timestamp at microsecond resolution
     * string functions are used to avoid problems on 32 bit systems.
     */
    public function generateNonce(): string
    {
        $nonce = explode(' ', microtime());
        $this->nonce = $nonce[1].str_pad(substr($nonce[0], 2, 6), 6, '0');

        return $this->nonce;
    }

    /**
     * Decode json response from Bitstamp API.
     */
    protected function decodeResult($response): array
    {
        return \GuzzleHttp\json_decode(
            $response,
            true
        );
    }
}
