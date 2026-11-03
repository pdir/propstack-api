# Simple Propstack PHP API

## Requirements

PHP: >= 7.2  
Extensions: [Composer](https://getcomposer.org/), [PHP-JSON](https://www.php.net/manual/en/book.json.php)

## Install

composer:  
`composer require pdir/propstack-api`

## Usage

Search for the official API Documentation [here](https://docs.propstack.de/).  
You need an *Api Key* - Ask support or visit account settings in Propstack.

### Basic

```php
// store keys in .env file or use credentials array
$credentials = [
    'apiKey' => 'PROPSTACK_API_KEY', 
];

$api = new Pdir\Propstack\Api();
or
$api = new Pdir\Propstack\Api($credentials);

// get all projects
$projects = $api->getProjects();

// get one page of saved queries (Suchprofile)
$savedQueries = $api->getSavedQueries(['page' => 1, 'per' => 100]);

// get all saved queries (all pages)
$allSavedQueries = $api->getAllSavedQueries();

// get a single contact
$contact = $api->getContact(123);
```