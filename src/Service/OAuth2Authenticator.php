<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Service;

use Dogado\JsonApi\Client\Exception\Oauth2\AuthenticationException;
use Dogado\JsonApi\Client\Factory\Oauth2\CredentialFactoryInterface;
use Dogado\JsonApi\Client\Model\OAuth2Credentials;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

class OAuth2Authenticator
{
    public function __construct(
        protected ClientInterface $httpClient,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory,
        protected CredentialFactoryInterface $authStorageFactory
    ) {
    }

    /**
     * @throws AuthenticationException
     */
    public function withClientCredentials(
        UriInterface $endpointUri,
        string $clientId,
        string $clientSecret
    ): OAuth2Credentials {
        $payload = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];

        $payload = json_encode($payload);
        if (false === $payload) {
            throw AuthenticationException::unableToEncodePayload(json_last_error_msg());
        }

        try {
            $request = $this->requestFactory
                ->createRequest('POST', $endpointUri)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($payload));

            $response = $this->httpClient->sendRequest($request);
        } catch (Throwable $e) {
            throw AuthenticationException::failed(null, $e);
        }

        return $this->authStorageFactory->fromAuthorityResponse($response);
    }
}
