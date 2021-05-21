# Abstract client side php implementation of the JSON:API protocol.

## Installation

```sh
composer require dogado/json-api-client
```

It's recommended to install `guzzlehttp/guzzle` version `^7.0` as http-client and `http-interop/http-factory-guzzle` for [PSR-17](https://www.php-fig.org/psr/psr-17/) compatible factories.

```sh
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle
```

You can also use any other HTTP client which implements [PSR-18](https://www.php-fig.org/psr/psr-18/).

## Usage

First you should read the docs of [dogado/json-api-common](https://github.com/dogado-group/json-api-common/tree/main/docs) where all basic structures will be explained.

Your API client is an instance of `Dogado\JsonApi\Client\JsonApiClient`, which requires a [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client (`Psr\Http\Client\ClientInterface`) to execute requests.

```php
use Dogado\JsonApi\Client\JsonApiClient;
use Dogado\JsonApi\Client\Factory\RequestFactory;
use Dogado\JsonApi\Client\Validator\ResponseValidator;

$client = new JsonApiClient(
    $httpClient, // instance of Psr\Http\Client\ClientInterface
    $psrRequestFactory, // instance of Psr\Http\Message\RequestFactoryInterface
    $streamFactory, // instance of Psr\Http\Message\StreamFactoryInterface
    $serializer, // instance of Dogado\JsonApi\Serializer\DocumentSerializerInterface
    $responseFactory, // instance of Dogado\JsonApi\Client\Response\ResponseFactoryInterface
);

$baseUrl = new Uri('http://example.com/api');
$requestFactory = new RequestFactory($baseUrl);

$request = $requestFactory->createGetRequest(new Uri('/myResource/1')); // will fetch the resource at http://example.com/api/myResource/1
$response = $client->execute($request);

// OPTIONAL: Validate the response to match your needs. See the ResponseValidator class for all assertion methods
(new ResponseValidator())->assertResourcesMatchTypeAndContainIds($response, 'myResource');

$document = $response->document();
$myResource = $document->data()->first(); // the resource fetched by this request
$myIncludedResources = $document->included()->all(); // the included resources fetched with the include parameter
```

### Action Pattern

In most cases it's easier to capsule request scenarios into single classes since every request has its own requirements.
In this package this is called `Action`. To make things easier, we already defined an `AbstractAction` class under the
`Dogado\JsonApi\Client\Action` namespace. An example how to create such an action can be found
[in the tests](./tests/Action/DummyAction.php).

When fetching resources in actions it's also very common to filter, paginate and sort. To define these options within Actions,
there are multiple Traits you can use, defined in the same namespace as the `AbstractAction` class.

## Credits

- [Chris Döhring](https://github.com/chris-doehring)
- [Philipp Marien](https://github.com/pmarien)
- [eosnewmedia team](https://github.com/eosnewmedia)

This package contains code taken from [enm/json-api-client](https://github.com/eosnewmedia/JSON-API-Client).

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
