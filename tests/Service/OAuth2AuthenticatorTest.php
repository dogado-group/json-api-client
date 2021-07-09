<?php

namespace Dogado\JsonApi\Client\Tests\Service;

use Dogado\JsonApi\Client\Exception\Oauth2\AuthenticationException;
use Dogado\JsonApi\Client\Factory\Oauth2\CredentialFactoryInterface;
use Dogado\JsonApi\Client\Model\OAuth2Credentials;
use Dogado\JsonApi\Client\Service\OAuth2Authenticator;
use Dogado\JsonApi\Client\Tests\TestCase;
use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class OAuth2AuthenticatorTest extends TestCase
{
    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;
    protected CredentialFactoryInterface $credentialFactory;
    protected OAuth2Authenticator $authenticator;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->credentialFactory = $this->createMock(CredentialFactoryInterface::class);
        $this->authenticator = new OAuth2Authenticator(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->credentialFactory
        );
    }

    public function test(): void
    {
        $endpointUri = $this->createMock(UriInterface::class);
        $clientId = $this->faker()->uuid();
        $clientSecret = $this->faker()->password();

        $payload = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];

        $request = $this->createMock(RequestInterface::class);
        $this->requestFactory->expects(self::once())->method('createRequest')->with('POST', $endpointUri)
            ->willReturn($request);
        $request->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json')
            ->willReturnSelf();

        $stream = $this->createMock(StreamInterface::class);
        $this->streamFactory->expects(self::once())->method('createStream')->with(json_encode($payload))
            ->willReturn($stream);

        $request->expects(self::once())->method('withBody')->with($stream)->willReturnSelf();

        $response = $this->createMock(ResponseInterface::class);
        $this->httpClient->expects(self::once())->method('sendRequest')->with($request)->willReturn($response);

        $authStorage = $this->createMock(OAuth2Credentials::class);
        $this->credentialFactory->expects(self::once())->method('fromAuthorityResponse')->with($response)
            ->willReturn($authStorage);

        $this->assertEquals(
            $authStorage,
            $this->authenticator->withClientCredentials($endpointUri, $clientId, $clientSecret)
        );
    }

    public function testInvalidPayload(): void
    {
        $endpointUri = $this->createMock(UriInterface::class);
        $clientId = $this->faker()->uuid();
        $clientSecret = utf8_decode('äöä');

        json_encode($clientSecret);
        $this->expectExceptionObject(AuthenticationException::unableToEncodePayload(json_last_error_msg()));
        $this->authenticator->withClientCredentials($endpointUri, $clientId, $clientSecret);
    }

    public function testHttpError(): void
    {
        $endpointUri = $this->createMock(UriInterface::class);
        $clientId = $this->faker()->uuid();
        $clientSecret = $this->faker()->password();

        $payload = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];

        $request = $this->createMock(RequestInterface::class);
        $this->requestFactory->expects(self::once())->method('createRequest')->with('POST', $endpointUri)
            ->willReturn($request);
        $request->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json')
            ->willReturnSelf();

        $stream = $this->createMock(StreamInterface::class);
        $this->streamFactory->expects(self::once())->method('createStream')->with(json_encode($payload))
            ->willReturn($stream);

        $request->expects(self::once())->method('withBody')->with($stream)->willReturnSelf();

        $e = $this->createMock(Exception::class);
        $this->httpClient->expects(self::once())->method('sendRequest')->with($request)->willThrowException($e);

        $this->expectExceptionObject(AuthenticationException::failed(null, $e));
        $this->authenticator->withClientCredentials($endpointUri, $clientId, $clientSecret);
    }
}
