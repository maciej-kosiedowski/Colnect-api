# Colnect API – PHP SDK

A lightweight, fully typed PHP SDK for the [Colnect](https://colnect.com) REST API, built on top of [Saloon v3](https://docs.saloon.dev). It ships with first-class support for HMAC request signing, request mocking, PHPStan level 9 static analysis and mutation testing via Infection.

[![Tests](https://github.com/maciej-kosiedowski/Colnect-api/actions/workflows/tests.yml/badge.svg)](https://github.com/maciej-kosiedowski/Colnect-api/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-8892BF.svg)](https://www.php.net/)
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
- [Mocking & testing your integration](#mocking--testing-your-integration)
- [Sentry integration](#sentry-integration)
- [Development](#development)
  - [Running the test suite](#running-the-test-suite)
  - [Static analysis](#static-analysis)
  - [Mutation testing](#mutation-testing)
- [Documentation](#documentation)
- [Versioning](#versioning)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)
- [Credits](#credits)

---

## Features

- **Saloon v3 connector** with a clean, request-per-endpoint architecture.
- **Built-in HMAC-SHA256 signing** of every outgoing request (`Capi-Timestamp` + `Capi-Hash` headers).
- **Locale aware** – the connector automatically routes requests through the localized Colnect endpoint (`/en`, `/pl`, `/fr`, …).
- **Strongly typed** with `declare(strict_types=1)` everywhere and an enum-driven catalog of collectable categories (`CategoryType`).
- **Mock-friendly** – integrates seamlessly with Saloon's `MockClient` / `MockResponse` for unit testing your own application.
- **Quality-first**:
  - PHPUnit 10
  - PHPStan **level 9**
  - Infection MSI (`@default` mutators) with `pcov` coverage
- **PSR-4 autoloaded** library with zero runtime dependencies beyond Saloon.

## Requirements

| Component        | Version                |
| ---------------- | ---------------------- |
| PHP              | `^8.2`                 |
| saloonphp/saloon | `^3.0`                 |
| ext-pcov         | dev only (for Infection coverage) |

## Installation

Install the package via Composer:

```bash
composer require vendor/colnect-api
```

> Replace `vendor/colnect-api` with the actual Packagist name once the package is published. The autoload root is `Vendor\ColnectApi\` and maps to `src/`.

## Quick start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Vendor\ColnectApi\ColnectConnector;
use Vendor\ColnectApi\Enums\CategoryType;
use Vendor\ColnectApi\Requests\Category\GetCountriesRequest;
use Vendor\ColnectApi\Requests\General\GetCategoriesRequest;
use Vendor\ColnectApi\Requests\Search\GlobalSearchRequest;

$connector = new ColnectConnector(
    appId:     getenv('COLNECT_APP_ID'),
    appSecret: getenv('COLNECT_APP_SECRET'),
    language:  'en',
);

// 1. List every supported collectable category
$categories = $connector->send(new GetCategoriesRequest())->json();

// 2. List the countries that issued a given collectable category
$countries = $connector
    ->send(new GetCountriesRequest(CategoryType::Stamps))
    ->json();

// 3. Search across a collectable type
$results = $connector
    ->send(new GlobalSearchRequest('stamps', 'olympic games', page: 2))
    ->json();
```

## Authentication

Colnect uses an `appId` + `appSecret` pair that must be requested from Colnect. Every request is signed with an HMAC-SHA256 hash:

```
Capi-Timestamp: <unix-timestamp>
Capi-Hash:      hash_hmac('sha256', path + '>|<' + timestamp, appSecret)
```

The signing logic lives in `ColnectConnector::boot()` and is applied automatically to every request – you never have to touch it yourself.

```php
$connector = new ColnectConnector(
    appId:     'your-app-id',
    appSecret: 'your-app-secret',
    language:  'pl', // resolves to https://api.colnect.net/pl/api/{appId}
);
```

> **Never** commit your `appSecret` to version control. Load it from environment variables, a secrets manager, or your framework's configuration layer.

## Available requests

| Namespace                                    | Class                       | Endpoint                                          |
| -------------------------------------------- | --------------------------- | ------------------------------------------------- |
| `Vendor\ColnectApi\Requests\General`         | `GetCategoriesRequest`      | `GET /categories`                                 |
| `Vendor\ColnectApi\Requests\General`         | `GetLanguagesRequest`       | `GET /languages`                                  |
| `Vendor\ColnectApi\Requests\Category`        | `GetCountriesRequest`       | `GET /countries/cat/{category}`                   |
| `Vendor\ColnectApi\Requests\Category`        | `GetItemsListRequest`       | `GET /items/cat/{category}`                       |
| `Vendor\ColnectApi\Requests\Search`          | `GlobalSearchRequest`       | `GET /search/collectibles/{type}/q/{query}[/page/{n}]` |

All request classes follow the same pattern:

```php
$request  = new GetCountriesRequest(CategoryType::Coins);
$response = $connector->send($request);

$body   = $response->body();   // raw string
$json   = $response->json();   // array
$status = $response->status(); // int
```

## Working with categories

The `CategoryType` enum provides a typed list of all collectable categories supported by Colnect:

```php
use Vendor\ColnectApi\Enums\CategoryType;

CategoryType::Coins;       // 'coins'
CategoryType::Stamps;      // 'stamps'
CategoryType::Banknotes;   // 'banknotes'
CategoryType::TradeCards;  // 'tradecards'
// …and 40+ more
```

Each category-aware request accepts either the enum **or** a raw string, so you can interoperate with values coming from forms, configs, or HTTP input:

```php
new GetCountriesRequest(CategoryType::Stamps); // typed
new GetCountriesRequest('stamps');             // string, still valid
```

## Mocking & testing your integration

Because the SDK is built on Saloon, you can mock every response without hitting the network – ideal for fast, deterministic unit tests:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Vendor\ColnectApi\ColnectConnector;
use Vendor\ColnectApi\Requests\General\GetLanguagesRequest;

$mockClient = new MockClient([
    GetLanguagesRequest::class => MockResponse::make([
        ['code' => 'en', 'name' => 'English'],
        ['code' => 'pl', 'name' => 'Polski'],
    ], status: 200),
]);

$connector = (new ColnectConnector('app_id', 'app_secret'))
    ->withMockClient($mockClient);

$languages = $connector->send(new GetLanguagesRequest())->json();
```

See [`tests/ColnectConnectorTest.php`](tests/ColnectConnectorTest.php) for end-to-end examples of asserting headers, payloads and the HMAC signature.

## Sentry integration

The SDK ships with an optional [Sentry](https://sentry.io) integration that records an HTTP breadcrumb for every outgoing request and reports failed responses or fatal transport exceptions as Sentry events. It is implemented as Saloon middleware so it sits transparently in front of every request you send.

Install the Sentry SDK alongside this package:

```bash
composer require sentry/sentry:^4.0
```

Initialize Sentry once during your application's bootstrap, then attach the integration to the connector:

```php
use Sentry\State\Hub;
use Vendor\ColnectApi\ColnectConnector;
use Vendor\ColnectApi\Sentry\SentryIntegration;

\Sentry\init([
    'dsn' => getenv('SENTRY_DSN'),
]);

$connector = new ColnectConnector(
    appId:     getenv('COLNECT_APP_ID'),
    appSecret: getenv('COLNECT_APP_SECRET'),
);

SentryIntegration::register($connector);
```

What you get:

- **Breadcrumbs** – one `http.client` breadcrumb per outgoing request and one per response (with the HTTP method, URL and status code).
- **Exception capture** – any failed response (the connector throws by default on 4xx/5xx) and any fatal transport error are sent to Sentry via `captureException()`.
- **Safe by default** – when `sentry/sentry` is not installed the integration is a complete no-op, so this SDK keeps zero runtime dependencies beyond Saloon.

The integration is opt-in; nothing is registered automatically. See [`tests/Sentry/SentryIntegrationTest.php`](tests/Sentry/SentryIntegrationTest.php) for a worked example using a stub Sentry transport.

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

- **API reference** – the source of truth for available endpoints is the official [Colnect API documentation](https://colnect.com/en/help/api). This SDK is a thin, typed wrapper over those endpoints.
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
        "vendor/colnect-api": "^1.0"
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
- **Built with:** [Saloon](https://docs.saloon.dev) by Sam Carré
- **Data provider:** [Colnect](https://colnect.com) – the largest online collectors' community

If this package is useful to you, please consider [starring the repository on GitHub](https://github.com/maciej-kosiedowski/Colnect-api) ⭐.
