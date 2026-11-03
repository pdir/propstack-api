# Changelog

## [0.2.0](https://github.com/pdir/propstack-api/tree/0.1.0) – 2026-11-03

- [Security] Remove `var_dump()` of the API key in the constructor
- [Security] Send the API key as `X-API-KEY` header instead of the `api_key` query parameter
- [Added] `getAllSavedQueries()` (walks through all pages via `page`/`per`) and `getContact()` (`getContacts()` kept as deprecated alias)
- [Added] Optional query parameters for all getters, injectable HTTP client, 30s timeout
- [Fixed] TypeError when the API answers with a non-200 status code
- [Fixed] Missing API key now throws a `PropstackApiException` instead of sending unauthenticated requests

## [0.1.0](https://github.com/pdir/propstack-api/tree/0.1.0) – 2022-01-06

- [feature] Retrieve projects
- [feature] Retrieve search profiles
