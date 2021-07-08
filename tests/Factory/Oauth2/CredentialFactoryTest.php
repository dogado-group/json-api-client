<?php

namespace Dogado\JsonApi\Client\Tests\Factory\Oauth2;

use DateInterval;
use DateTime;
use Dogado\JsonApi\Client\Exception\Oauth2\AuthenticationException;
use Dogado\JsonApi\Client\Factory\Oauth2\CredentialFactory;
use Dogado\JsonApi\Client\Model\OAuth2Credentials;
use Dogado\JsonApi\Client\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CredentialFactoryTest extends TestCase
{
    public function testConversion(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response->method('getBody')->willReturn($stream);

        $responseData = [
            'token_type' => $this->faker()->slug(),
            'access_token' => $this->faker()->md5(),
            'expires_in' => $this->faker()->numberBetween(),
        ];
        $stream->method('getContents')->willReturn(json_encode($responseData));

        $expiresAt = (new DateTime())->add(new DateInterval('PT' . ((int) $responseData['expires_in'] - 5) . 'S'));
        $expected = new OAuth2Credentials($responseData['token_type'], $responseData['access_token'], $expiresAt);
        $actual = (new CredentialFactory())->fromAuthorityResponse($response);

        $this->assertInstanceOf(OAuth2Credentials::class, $actual);
        $this->assertEquals($expected->tokenType, $actual->tokenType);
        $this->assertEquals($expected->accessToken, $actual->accessToken);
        $this->assertEquals($expected->expiresAt->getTimestamp(), $actual->expiresAt->getTimestamp());
    }

    public function testInvalidExpirationInterval(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response->method('getBody')->willReturn($stream);

        $responseData = [
            'token_type' => $this->faker()->slug(),
            'access_token' => $this->faker()->md5(),
            'expires_in' => -1 * $this->faker()->numberBetween(),
        ];
        $stream->method('getContents')->willReturn(json_encode($responseData));

        $this->expectExceptionObject(AuthenticationException::unableToDetermineExpiration($responseData['expires_in']));
        (new CredentialFactory())->fromAuthorityResponse($response);
    }

    public function testMissingResult(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response->method('getBody')->willReturn($stream);

        $stream->method('getContents')->willReturn(json_encode(['']));

        $missing = [
            'token_type',
            'access_token',
            'expires_in',
        ];
        $this->expectExceptionObject(AuthenticationException::missingDataInResponse($missing));
        (new CredentialFactory())->fromAuthorityResponse($response);
    }

    public function testEmptyResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response->method('getBody')->willReturn($stream);

        $stream->method('getContents')->willReturn(null);

        $this->expectExceptionObject(AuthenticationException::unableToDecodeResponse()->setResponse($response));
        (new CredentialFactory())->fromAuthorityResponse($response);
    }

    public function testInvalidJson(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response->method('getBody')->willReturn($stream);

        $stream->method('getContents')->willReturn($this->faker()->text());

        $this->expectExceptionObject(AuthenticationException::unableToDecodeResponse()->setResponse($response));
        (new CredentialFactory())->fromAuthorityResponse($response);
    }

    public function testErrorResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response->method('getBody')->willReturn($stream);

        $errorSlug = $this->faker()->slug();
        $stream->method('getContents')->willReturn(json_encode(['error' => $errorSlug]));

        $this->expectExceptionObject(AuthenticationException::failed($errorSlug)->setResponse($response));
        (new CredentialFactory())->fromAuthorityResponse($response);
    }
}
