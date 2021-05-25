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
    $authMiddleware // optional instance of Dogado\JsonApi\Client\Middleware\AuthenticationMiddlewareInterface. See docs below.
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

### Authentication

When using a JSON:API client to access a server application you will probably need to be authenticated. As this a common
use case, this client offers native support for authentication as so called "AuthenticationMiddleware" represented by
an interface. The client provides a native set of authentication mechanisms: OAuth2 client credentials and HTTP basic
auth, but you can create a custom middleware yourself based on the `AuthenticationMiddlewareInterface` if that doesn't
fit your needs or feel free to create a [pull request](https://github.com/dogado-group/json-api-client/pulls) to add
more authentications. 

```php
use Dogado\JsonApi\Serializer\Deserializer;
use Dogado\JsonApi\Serializer\Serializer;
use Dogado\JsonApi\Client\JsonApiClient;
use Dogado\JsonApi\Client\Response\ResponseFactory;
use Dogado\JsonApi\Client\Middleware\AuthenticationMiddleware;

/** @var Psr\Http\Client\ClientInterface $httpClient */
/** @var Psr\Http\Message\RequestFactoryInterface $requestFactory */
/** @var Psr\Http\Message\StreamFactoryInterface $streamFactory */
/** @var Psr\Http\Message\UriFactoryInterface $uriFactory */

// define which authentication you want to use (you can also leave the middleware `null` in order to use no authentication)
$authenticationMiddleware = new AuthenticationMiddleware();
$client = new JsonApiClient(
    $httpClient,
    $requestFactory,
    $streamFactory,
    new Serializer(),
    new ResponseFactory(
        new Deserializer()
    ),
    $authenticationMiddleware
);

###### HTTP basic auth
use Dogado\JsonApi\Client\Model\BasicCredentials;
$authenticationMiddleware->setBasicCredentials(new BasicCredentials('username', 'password'));

###### OAuth 2 client credentials
use Dogado\JsonApi\Client\Factory\Oauth2\CredentialFactory;
use Dogado\JsonApi\Client\Service\OAuth2Authenticator;
use Dogado\JsonApi\Client\Exception\Oauth2\AuthenticationException;

# the Authenticator class also allows you to overload the HTTP client and the auth storage factory it uses
$authenticator = new OAuth2Authenticator($httpClient, $requestFactory, $streamFactory, new CredentialFactory());
try {
    $oauth2Credentials = $authenticator->withClientCredentials($uriFactory->createUri('https://server.local/oauth/token'), 'Client-ID', 'Client-Secret');
    $authenticationMiddleware->setOAuth2Credentials($oauth2Credentials);
} catch (AuthenticationException $e) {
    // escalate any errors accordingly
}
```

#### OAuth2
The `AuthenticationMiddleware` class requires a `Dogado\JsonApi\Client\Model\OAuth2Credentials` instance which
contains an access token, the token type and the expiration date. You can either create an instance yourself and insert
the required data or use the `Dogado\JsonApi\Client\Service\OAuth2Authenticator` class to generate them.

Please cache the `OAuth2Credentials` instance as long as the method `isExpired` is not `true`, to prevent unnecessary
authentication calls.

As the authentication endpoint does not follow the JSON:API protocol, it also throws no JSON:API specific errors or
exceptions. The only relevant errors are variations of the
`Dogado\JsonApi\Client\Exception\Oauth2\AuthenticationException` class which differentiates between error message
and code. Such an exception instance also contains the plain response as `Psr\Http\Message\ResponseInterface` to have
more context to an error.

## Credits

- [Chris Döhring](https://github.com/chris-doehring)
- [Philipp Marien](https://github.com/pmarien)
- [eosnewmedia team](https://github.com/eosnewmedia)

This package contains code taken from [enm/json-api-client](https://github.com/eosnewmedia/JSON-API-Client).

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
