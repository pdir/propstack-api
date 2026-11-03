# Simple Propstack PHP API

Lightweight PHP client for the [Propstack API v1](https://docs.propstack.de/).

## Requirements

PHP: >= 7.2 (PHP 8 supported)  
Extensions: [PHP-JSON](https://www.php.net/manual/en/book.json.php), [cURL](https://www.php.net/manual/en/book.curl.php)  
Dependencies: [Guzzle](https://docs.guzzlephp.org/) 6.3, 7 or 8 (installed via [Composer](https://getcomposer.org/))

## Install

composer:  
`composer require pdir/propstack-api`

## Usage

Search for the official API Documentation [here](https://docs.propstack.de/).  
You need an *API key* - ask support or visit the account settings in Propstack.

### Authentication

The API key is taken from the `apiKey` credential or, if not given, from the environment variable `PROPSTACK_API_KEY`.
It is sent as `X-API-KEY` header, so it does not show up in URLs, server logs or proxies.

```php
// key from environment variable PROPSTACK_API_KEY
$api = new Pdir\Propstack\Api();

// or pass the key explicitly
$api = new Pdir\Propstack\Api([
    'apiKey' => 'PROPSTACK_API_KEY',
]);
```

If no key is configured, every request throws a `PropstackApiException`.

### Basic

```php
// get all projects
$projects = $api->getProjects();

// get one page of saved queries (Suchprofile)
$savedQueries = $api->getSavedQueries(['page' => 1, 'per' => 100]);

// get all saved queries (walks through all pages)
$allSavedQueries = $api->getAllSavedQueries();
// $allSavedQueries['data']               => all saved queries
// $allSavedQueries['meta']['total_count'] => number of loaded saved queries

// optional: filter and page size (default 100, Propstack recommends max. 500)
$allSavedQueries = $api->getAllSavedQueries(['client' => 42], 500);

// get a single contact
$contact = $api->getContact(123);

// get a single contact with related data
$contact = $api->getContact(123, ['include' => 'children,documents,relationships,owned_properties']);
```

All getters accept an optional array of query parameters that is passed to the Propstack API.

### Methods

| Method | Endpoint | Description |
|---|---|---|
| `getProjects(array $query = [])` | `GET /v1/projects` | Projects |
| `getSavedQueries(array $query = [])` | `GET /v1/saved_queries` | One page of saved queries (`client`, `page`, `per`) |
| `getAllSavedQueries(array $query = [], int $perPage = 100)` | `GET /v1/saved_queries` | All saved queries across all pages |
| `getContact(int $id, array $query = [])` | `GET /v1/contacts/{id}` | A single contact (`include`) |
| `getContacts($id, array $query = [])` | `GET /v1/contacts/{id}` | **Deprecated**, use `getContact()` |
| `request(string $method, string $resource, array $options = [])` | any | Low-level request, returns the PSR-7 response |

Getters return the decoded JSON as array, or `null` if the API answers with a status code other than 200.

### Error handling

Failed requests (network errors, 4xx/5xx responses, invalid JSON) throw a
`Pdir\Propstack\Exceptions\PropstackApiException`. The original Guzzle exception is available via `getPrevious()`.

```php
use Pdir\Propstack\Exceptions\PropstackApiException;

try {
    $contact = $api->getContact(123);
} catch (PropstackApiException $e) {
    // e.g. log $e->getMessage(), $e->getCode()
}
```

### Custom HTTP client

You can inject your own Guzzle client, e.g. for different timeouts, proxies, middleware or tests.
The default client uses a timeout of 30 seconds.

```php
$client = new GuzzleHttp\Client([
    'timeout' => 60,
    'headers' => ['Accept' => 'application/json'],
]);

$api = new Pdir\Propstack\Api(['apiKey' => 'PROPSTACK_API_KEY'], $client);
```

## Upgrade from 0.1.0

- The API key is now sent as `X-API-KEY` header instead of the `api_key` query parameter.
- `getContacts()` is deprecated, use `getContact()` instead.
- A missing API key now throws a `PropstackApiException` instead of sending unauthenticated requests.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT, see [LICENSE](LICENSE).
