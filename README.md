eLife API SDK for PHP
=====================

This library provides a PHP SDK for the [eLife Sciences API](https://github.com/elifesciences/api-raml).

It includes code from the [CsaGuzzleBundle](https://github.com/csarrazi/CsaGuzzleBundle) for testing, which in the future will be a normal dependency (see [csarrazi/CsaGuzzleBundle#169](https://github.com/csarrazi/CsaGuzzleBundle/issues/169)).

Dependencies
------------

* [Composer](https://getcomposer.org/)
* PHP 7

Installation
------------

Execute `composer require elife/api-sdk`.


Usage (ApiClient)
-----------------

The `eLife\ApiSdk\ApiClient` namespace provides separate clients for each part of the eLife API.

Each method on an API client represents an endpoint.

You can pass default headers to an API client, and/or to each API client method. You should provide an `Accept` header stating which versions you support.

API clients always return instances of `GuzzleHttp\Promise\PromiseInterface`, which wrap instances of `eLife\ApiClient\Result`, which in turn wrap the JSON response.

`eLife\ApiClient\Result` provides integration with the [JMESPath](http://jmespath.org/) (using [jmespath.php](https://github.com/jmespath/jmespath.php)), to allow easy searching of JSON responses.

### Basic example

To list the Labs Post IDs that appear on the first page of the endpoint:

```php
use eLife\ApiSdk\ApiClient\LabsClient;
use eLife\ApiClient\HttpClient\Guzzle6HttpClient;
use eLife\ApiClient\MediaType;
use GuzzleHttp\Client as Guzzle;

$guzzle = new Guzzle(['base_uri' => 'https://api.elifesciences.org/']);
$httpClient = new Guzzle6HttpClient($guzzle);
$labsClient = new LabsClient($httpClient);

var_dump($labsClient->listPosts(['Accept' => new MediaType(LabsClient::TYPE_POST_LIST, 1)])->wait()->search('items[*].id'));
```
