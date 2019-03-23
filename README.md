## This project is still work in progress, and not finished yet!
# bitstamp-api-client
Client for Bitstamp.net HTTP API v2 with support for Laravel

*The structure of this package was strongly influenced by the design of the [kraken-api-client](https://github.com/butschster/kraken-api-client) from Butschster.*

# Install
```composer require bitbuy-at/bitstamp-api-client```

## Laravel
### Laravel 5.5+
If you're using Laravel 5.5 or above, the package will automatically register the Bitstamp provider and facade.

### Laravel 5.4 and below
Add `bitbuyAT\Bitstamp\BitstampServiceProvider` to the providers array in your `config/app.php`:

```php
'providers' => [
    // Other service providers...

    bitbuyAT\Bitstamp\BitstampServiceProvider::class,
],
```
If you want to use the facade interface, you can use the facade class when needed:
```
use bitbuyAT\Bitstamp\Facade\Bitstamp;
```
Or add an alias in your `config/app.php`:
```php
'aliases' => [
    ...
    'Bitstamp' => bitbuyAT\Bitstamp\Facade\Bitstamp::class,
],
```
## Configuration
You can update your .env file with the following settings (only needed for private calls):
```
BITSTAMP_KEY=key
BITSTAMP_SECRET=secret
BITSTAMP_CLIENT_ID=client-id
```
## Usage

### Get current prices
```php
use bitbuyAT\Bitstamp\Facade\Bitstamp;

$prices = Bitstamp::getTicker($tradingPair);
$prices->getData();
$prices->askPrice();
$prices->bidPrice();
```

*More examples will follow soon.*

# Contributing
Want to contribute? Great!

Create a new issue first, describing the feature or bug.

Just fork our code, make your changes, then let us know and we will review it.

1. Fork it.
2. Create a feature branch (git checkout -b my_feature)
3. Commit your changes (git commit -m "Added My Feature")
4. Push to the branch (git push origin my_feature)
5. Open a [Pull Request](https://github.com/bitbuyAT/bitstamp-api-client/compare)
6. Enjoy and wait ;)

We are constantly updating and improving our code. We hope it can be for the benefit of the entire community.

# License
MIT License

Please check [LICENSE.txt](https://github.com/bitbuyAT/bitstamp-api-client/blob/master/LICENSE.txt)


