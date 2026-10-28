# Colnect API – PHP SDK

A lightweight, fully typed PHP SDK for the [Colnect](https://colnect.com) REST API, built on top of [Saloon v3](https://docs.saloon.dev). It ships with first-class support for HMAC request signing, request mocking, PHPStan level 9 static analysis and mutation testing via Infection.

[![Tests](https://github.com/maciej-kosiedowski/Colnect-api/actions/workflows/tests.yml/badge.svg)](https://github.com/maciej-kosiedowski/Colnect-api/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/badge/php-8.2%20%7C%208.3%20%7C%208.4-8892BF.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](phpstan.neon)

---

## Table of contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Authentication](#authentication)
- [Available requests](#available-requests)
- [Working with categories](#working-with-categories)
- [Filtering lists](#filtering-lists)
- [Items and pictures](#items-and-pictures)
- [Marketplace](#marketplace)
- [Image search](#image-search)
- [Error handling](#error-handling)
- [Mocking & testing your integration](#mocking--testing-your-integration)
- [Development](#development)
  - [Running the test suite](#running-the-test-suite)
  - [Static analysis](#static-analysis)
  - [Mutation testing](#mutation-testing)
- [Documentation](#documentation)
- [Versioning](#versioning)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)
- [Attribution required by Colnect](#attribution-required-by-colnect)
- [Credits](#credits)

---

## Features

- **Full CAPI coverage** - general, collector, category, search, image search and marketplace actions.
- **Saloon v3 connector** with a clean, request-per-endpoint architecture.
- **Built-in HMAC-SHA256 signing** of every outgoing request (`Capi-Timestamp` + `Capi-Hash` headers).
- **HTTP/2 by default** - CAPI rejects HTTP/1.x with `426 Upgrade Required`, so the connector asks Guzzle for HTTP/2.
- **Locale aware** - the connector automatically routes requests through the localized Colnect endpoint (`/en`, `/pl`, `/fr`, ...).
- **Composable filters** - the documented common list filters (and the undocumented ones) through an immutable `Filters` object.
- **Picture URL builder** - turns picture IDs into thumbnail or full size image URLs, including the `urlize()` slug rules.
- **Strongly typed** with `declare(strict_types=1)` everywhere and an enum-driven catalog of collectable categories (`CategoryType`).
- **Fails fast** - malformed IDs, categories, page numbers or user agents throw before anything reaches the wire.
- **Mock-friendly** - integrates seamlessly with Saloon's `MockClient` / `MockResponse` for unit testing your own application.
- **Quality-first**:
  - PHPUnit 10
  - PHPStan **level 9**
  - Infection MSI (`@default` mutators) with `pcov` coverage
- **PSR-4 autoloaded** library with zero runtime dependencies beyond Saloon.

## Requirements

| Component        | Version                |
| ---------------- | ---------------------- |
| PHP              | `^8.2` (tested on 8.2, 8.3 and 8.4) |
| saloonphp/saloon | `^3.0`                 |
| ext-pcov         | dev only (for Infection coverage) |

CAPI is served exclusively over HTTP/2, so the cURL build used by Guzzle has to support it (`curl --version` must list `HTTP2`).

## Installation

Install the package via Composer:

```bash
composer require slimad/colnect-api
```

> The package is distributed under the `slimad/colnect-api` name. The autoload root is `Slimad\ColnectApi\` and maps to `src/`.

## Quick start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Slimad\ColnectApi\ColnectConnector;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Requests\Category\GetCountriesRequest;
use Slimad\ColnectApi\Requests\Category\GetItemsListRequest;
use Slimad\ColnectApi\Requests\General\GetCategoriesRequest;
use Slimad\ColnectApi\Requests\Search\GlobalSearchRequest;
use Slimad\ColnectApi\Support\Filters;

$connector = new ColnectConnector(
    appId:     getenv('COLNECT_APP_ID'),
    appSecret: getenv('COLNECT_APP_SECRET'),
    language:  'en',
    userAgent: 'MyCoolApp/1.2.3.4',
);

// 1. List every supported collectable category
$categories = $connector->send(new GetCategoriesRequest())->json();

// 2. List the countries that issued a given collectable category
$countries = $connector
    ->send(new GetCountriesRequest(CategoryType::Stamps))
    ->json();

// 3. List the items of a country, newest first
$items = $connector
    ->send(new GetItemsListRequest(
        CategoryType::Stamps,
        Filters::make()->producer(2610)->year(1980),
    ))
    ->json();

// 4. Search across a collectable type
$results = $connector
    ->send(new GlobalSearchRequest(CategoryType::Stamps, 'olympic games', page: 2))
    ->json();
```

## Authentication

Colnect uses an `appId` + `appSecret` pair that must be [requested from Colnect](https://colnect.com/en/capi/apply_for_key). Every request is signed with an HMAC-SHA256 hash:

```
Capi-Timestamp: <unix-timestamp>
Capi-Hash:      hash_hmac('sha256', path + '>|<' + timestamp, appSecret)
```

The signing logic lives in `ColnectConnector::boot()` and is applied automatically to every request - you never have to touch it yourself.

```php
$connector = new ColnectConnector(
    appId:     'your-app-id',
    appSecret: 'your-app-secret',
    language:  'pl',                 // resolves to https://api.colnect.net/pl/api/{appId}
    userAgent: 'MyCoolApp/1.2.3.4',  // must clearly identify your app, 16+ characters
);
```

> **Never** commit your `appSecret` to version control. Load it from environment variables, a secrets manager, or your framework's configuration layer. If a secret leaks, revoke it and generate a new one - Colnect accepts both secrets for a short grace period while you rotate.

## Available requests

### General actions

| Class                                            | Endpoint                          | Notes |
| ------------------------------------------------ | --------------------------------- | ----- |
| `General\GetCategoriesRequest`                    | `GET /categories`                 | Category names currently available on Colnect. |
| `General\GetLanguagesRequest`                     | `GET /languages`                  | `short_iso => language_name`. |
| `General\GetCountryFlagsRequest`                  | `GET /country_flag/ids/{ids}`     | `country_id => flag_picture_id`, see `PictureUrl::flag()`. |
| `General\GetRequestCountRequest`                  | `GET /request_count[/days/{days}]`| Your own daily CAPI usage, up to 200 days. |
| `General\TranslatePhrasesRequest`                 | `POST /translate_phrases`         | Phrases are sent as a JSON array in the body. |

### Collector actions

| Class                                            | Endpoint                                  |
| ------------------------------------------------ | ----------------------------------------- |
| `Collector\GetRatingsCountRequest`                | `GET /ratings_count/collector/{collector}` |
| `Collector\GetRatingsReceivedRequest`             | `GET /ratings_received/collector/{collector}` |
| `Collector\GetRatingsGivenRequest`                | `GET /ratings_given/collector/{collector}` |

Privacy settings may make Colnect answer `private` instead of the data.

### Category actions

| Class                                            | Endpoint                                   | Mandatory arguments |
| ------------------------------------------------ | ------------------------------------------ | ------------------- |
| `Category\GetCountriesRequest`                    | `GET /countries/cat/{category}`            | - |
| `Category\GetProducersRequest`                    | `GET /producers/cat/{category}/country/{country}` | country |
| `Category\GetSeriesRequest`                       | `GET /series/cat/{category}/{country\|producer}/{producer}` | producer |
| `Category\GetYearsRequest`                        | `GET /years/cat/{category}/{country\|producer}/{producer}` | producer |
| `Category\GetThemesRequest`                       | `GET /themes/cat/{category}`               | - |
| `Category\GetCatalogsRequest`                     | `GET /catalogs/cat/{category}`             | - |
| `Category\GetCurrenciesRequest`                   | `GET /currencies/cat/{category}`           | - |
| `Category\GetFaceValuesRequest`                   | `GET /face_values/cat/{category}`          | - |
| `Category\GetSystemsRequest`                      | `GET /systems/cat/{category}`              | - |
| `Category\GetItemsListRequest`                    | `GET /list/cat/{category}/{filters}`       | at least one filter |
| `Category\GetItemIdsRequest`                      | `GET /list_id/cat/{category}/{filters}`    | at least one filter |
| `Category\GetItemRequest`                         | `GET /item/cat/{category}/id/{ids}`        | 1 to 50 item IDs |
| `Category\GetFieldTitlesRequest`                  | `GET /field_titles/cat/{category}`         | - |
| `Category\GetProducerIsCountryRequest`            | `GET /producer_is_country/cat/{category}`  | - |
| `Category\GetCategoryActionsRequest`              | `GET /category_actions/cat/{category}`     | - |
| `Category\GetCategorySortOptionsRequest`          | `GET /category_sort_options/cat/{category}`| - |

Not every action exists in every category - ask `GetCategoryActionsRequest` when in doubt.

### Search

| Class                                            | Endpoint                                                     |
| ------------------------------------------------ | ------------------------------------------------------------ |
| `Search\GlobalSearchRequest`                      | `GET /search[/collectibles/{type}]/q/{query}[/page/{page}]`  |
| `Search\ImageSearchRequest`                       | `POST /image_search[/cat/{category}]/{filters}`              |

### Marketplace

| Class                                            | Endpoint                                                          |
| ------------------------------------------------ | ----------------------------------------------------------------- |
| `Marketplace\GetMarketPricesRequest`              | `GET /market_prices/cat/{category}/ids/{ids}[/days/{days}]`       |
| `Marketplace\GetItemSalesRequest`                 | `GET /item_sales/cat/{category}/item_id/{ids}[/...]`              |

All request classes follow the same pattern:

```php
$request  = new GetCountriesRequest(CategoryType::Coins);
$response = $connector->send($request);

$body   = $response->body();   // raw string
$json   = $response->json();   // array
$status = $response->status(); // int
```

## Working with categories

The `CategoryType` enum provides a typed list of the collectable categories supported by Colnect, using the very module names Colnect expects in the `cat` filter:

```php
use Slimad\ColnectApi\Enums\CategoryType;

CategoryType::Coins;              // 'coins'
CategoryType::Stamps;             // 'stamps'
CategoryType::Banknotes;          // 'banknotes'
CategoryType::TradingCardGames;   // 'trading_card_games'
// ...46 categories in total
```

Each category-aware request accepts either the enum **or** a raw string, so you can interoperate with values coming from forms, configs, or HTTP input:

```php
new GetCountriesRequest(CategoryType::Stamps); // typed
new GetCountriesRequest('stamps');             // string, still valid
```

Colnect names the producer differently per category ("company" for phonecards, "brand" for teabags, the country itself for stamps). The enum knows the mapping:

```php
CategoryType::Phonecards->producerFilter();   // 'company'
CategoryType::Stamps->producerFilter();       // 'country'
CategoryType::Stamps->producerIsCountry();    // true
```

Categories are edited continuously on Colnect, so treat the enum as a convenience and `GetCategoriesRequest` as the source of truth.

## Filtering lists

`Filters` is an immutable collection rendering the documented common list filters as path segments, in the order you add them:

```php
use Slimad\ColnectApi\Support\Filters;

$filters = Filters::make()
    ->country(5)
    ->year(1980)
    ->theme(7)
    ->sort('face_value')   // see GetCategorySortOptionsRequest
    ->page(2);

$connector->send(new GetItemsListRequest(CategoryType::Stamps, $filters));
// GET /list/cat/stamps/country/5/year/1980/theme/7/sort/face_value/page/2
```

Named helpers exist for `country`, `producer`, `series`, `year`, `theme`, `catalog`, `currency`, `face_value`, `system`, `sort` and `page`. Colnect exposes more filters while you navigate the site; reach them with `with()`:

```php
Filters::make()->with('in_stock', 1);
```

Filter IDs are Colnect-internal and change over time - the API documentation asks you not to cache them for more than 24 hours.

## Items and pictures

`GetItemRequest` returns the fields listed by `GetFieldTitlesRequest`, in the same order. The fields differ per category and may change, so read them at runtime instead of hard-coding offsets:

```php
$fields = $connector->send(new GetFieldTitlesRequest(CategoryType::Coins))->json();
$item   = $connector->send(new GetItemRequest(CategoryType::Coins, 35693))->json();

// Up to 50 items per call - the response is then an array of arrays
$items = $connector->send(new GetItemRequest(CategoryType::Coins, [35693, 12594]))->json();

// Resolve referenced records (themes, catalog codes, ...) in the same call
$detailed = $connector->send(new GetItemRequest(CategoryType::Coins, 35693, includeDetails: true))->json();
```

Picture IDs become URLs through `PictureUrl`:

```php
use Slimad\ColnectApi\Enums\PictureSize;
use Slimad\ColnectApi\Support\PictureUrl;

PictureUrl::item(142891, 'Pasteur-Louis');
// https://i.colnect.net/images/t/142/891/Pasteur-Louis.jpg

PictureUrl::item(1234567, 'Some Name', PictureSize::Full);
// https://i.colnect.net/images/f/1234/567/Some_Name.jpg

PictureUrl::flag(469);
// https://i.colnect.net/flag/32/469-country.png
```

The item name is slugged with `Str::urlize()`, the port of the function published in the API specification.

## Marketplace

```php
use Slimad\ColnectApi\Enums\ItemsInSale;
use Slimad\ColnectApi\Requests\Marketplace\GetItemSalesRequest;
use Slimad\ColnectApi\Requests\Marketplace\GetMarketPricesRequest;

// Sale prices of the last 30 days, per condition
$prices = $connector
    ->send(new GetMarketPricesRequest(CategoryType::Coins, [35693, 12594], days: 30))
    ->json();

// Up to 20 single-item sales currently on the market
$sales = $connector
    ->send(new GetItemSalesRequest(CategoryType::Coins, 35693, ItemsInSale::Single, maxSales: 20))
    ->json();
```

## Image search

Image search is **disabled by default** and billed per call, so it has to be enabled for your application first. Because image recognition is imperfect, narrowing the search down with a category and filters is recommended.

```php
use Slimad\ColnectApi\Requests\Search\ImageSearchRequest;
use Slimad\ColnectApi\Support\ImageSearchTarget;

$target = ImageSearchTarget::fromUrl('https://example.com/coin-front.jpg')
    ->withBackUrl('https://example.com/coin-back.jpg'); // coins only

$matches = $connector
    ->send(new ImageSearchRequest($target, CategoryType::Coins, Filters::make()->country(5)))
    ->json();
```

A target can also be built from base64 data (`ImageSearchTarget::fromBase64()`) or from a local file (`ImageSearchTarget::fromFile()`), each with a `withBack*()` counterpart. The response carries `distances` and `best_matches`, where `c_id` is the item ID and `p_id` the picture ID.

## Error handling

Every malformed argument throws `Slimad\ColnectApi\Exceptions\InvalidArgumentException`, which extends the SPL `\InvalidArgumentException`:

```php
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;

try {
    new GetItemRequest(CategoryType::Coins, range(1, 51)); // 50 IDs max
} catch (InvalidArgumentException $e) {
    // 'Item IDs must not contain more than 50 IDs.'
}
```

HTTP errors are handled by Saloon as usual - use `$response->failed()`, `$response->status()` or `$connector->send()` with `->throw()`.

## Mocking & testing your integration

Because the SDK is built on Saloon, you can mock every response without hitting the network - ideal for fast, deterministic unit tests:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Slimad\ColnectApi\ColnectConnector;
use Slimad\ColnectApi\Requests\General\GetLanguagesRequest;

$mockClient = new MockClient([
    GetLanguagesRequest::class => MockResponse::make([
        'en' => 'English',
        'pl' => 'Polski',
    ], status: 200),
]);

$connector = (new ColnectConnector('app_id', 'app_secret'))
    ->withMockClient($mockClient);

$languages = $connector->send(new GetLanguagesRequest())->json();
```

See [`tests/ColnectConnectorTest.php`](tests/ColnectConnectorTest.php) for end-to-end examples of asserting headers, payloads and the HMAC signature.

## Development

Clone the repository and install dev dependencies:

```bash
git clone https://github.com/maciej-kosiedowski/Colnect-api.git
cd Colnect-api
composer install
```

### Running the test suite

```bash
composer test
# or
vendor/bin/phpunit
```

The test suite runs against:

- `tests/ColnectConnectorTest.php`       – connector + auth flow
- `tests/Requests/**/*Test.php`          – one test per request class

### Static analysis

PHPStan is configured at **level 9** for both `src/` and `tests/`:

```bash
composer analyse
# or
vendor/bin/phpstan analyse src tests --level 9
```

### Mutation testing

Infection is configured with the `@default` mutator set and source-only coverage:

```bash
composer mutate
# or
vendor/bin/infection --show-mutations --coverage=build/coverage
```

The `pcov` extension is required for coverage; it is already declared as a dev dependency in `composer.json`.

## Documentation

- **API reference** – the source of truth for available endpoints is the official [Colnect API documentation](https://colnect.com/en/help/capi). This SDK is a thin, typed wrapper over those endpoints. Not every action exists in every category, and Colnect takes requests for missing actions on the [CAPI forum](https://colnect.com/en/forum/viewforum!f=139).
- **Saloon documentation** – for advanced features such as request concurrency, retries, plugins, response DTOs, pagination, OAuth flows and caching, see the [Saloon v3 docs](https://docs.saloon.dev).
- **Source code** – every public class is annotated with PHPDoc. The fastest way to discover the surface area of this library is to browse [`src/`](src/).
- **Examples** – tests under [`tests/`](tests/) double as runnable usage examples for every request class.

## Versioning

This project follows [Semantic Versioning 2.0.0](https://semver.org/):

- **MAJOR** – incompatible API changes.
- **MINOR** – backwards-compatible feature additions (new requests, new enum cases, …).
- **PATCH** – backwards-compatible bug fixes and internal refactors.

Pin a major version in your `composer.json` to avoid unexpected breaking changes:

```json
{
    "require": {
        "slimad/colnect-api": "^1.0"
    }
}
```

## Contributing

Contributions are welcome and appreciated! To contribute:

1. **Fork** the repository and create a feature branch off `main`.
2. Add code **and** tests – PRs without tests will not be merged.
3. Make sure the full quality gate passes locally:
   ```bash
   composer test
   composer analyse
   composer mutate
   ```
4. Follow the existing coding style – `declare(strict_types=1)`, typed properties, PSR-12 formatting.
5. Open a pull request describing **what** changed and **why**.

Bug reports and feature requests can be filed via [GitHub Issues](https://github.com/maciej-kosiedowski/Colnect-api/issues). Please include a minimal reproducible example whenever possible.

## Security

**Please do not report security vulnerabilities through public GitHub issues, discussions, or pull requests.**

If you discover a security vulnerability in this package, report it privately so it can be addressed before public disclosure:

- **Preferred channel:** open a [GitHub private security advisory](https://github.com/maciej-kosiedowski/Colnect-api/security/advisories/new) for this repository.
- **Alternative:** email the maintainer directly at the address listed on the maintainer's [GitHub profile](https://github.com/maciej-kosiedowski). Use a clear subject such as `[SECURITY] Colnect-api – <short summary>`.

When reporting, please include:

1. A description of the vulnerability and the impact you believe it may have.
2. Step-by-step instructions to reproduce the issue (proof of concept code is ideal).
3. The affected version(s) of the package.
4. Any suggested remediation, if known.

You can expect:

- An initial acknowledgement within **72 hours**.
- A status update at least every **7 days** until the issue is resolved.
- Public disclosure (CVE if applicable) coordinated with you after a fix is released.

Please act in good faith and avoid:

- Exploiting the vulnerability beyond what is necessary to demonstrate it.
- Disclosing the issue publicly before a fix is available.
- Accessing data that is not yours.

## License

This package is open-sourced software licensed under the **MIT License**.

```
MIT License

Copyright (c) 2026 Maciej Kosiedowski
```

See the [LICENSE](LICENSE) file for the full text. In short, you are free to use, copy, modify, merge, publish, distribute, sublicense and/or sell copies of the software, provided the original copyright notice and the license text are included in all copies or substantial portions of the software. The software is provided "as is", without warranty of any kind.

## Credits

- **Author / Maintainer:** [Maciej Kosiedowski](https://github.com/maciej-kosiedowski)

If this package is useful to you, please consider [starring the repository on GitHub](https://github.com/maciej-kosiedowski/Colnect-api) ⭐.
