<?php

namespace Dogado\JsonApi\Client\Tests\Middleware;

use Dogado\JsonApi\Client\Middleware\AuthenticationMiddleware;
use Dogado\JsonApi\Client\Model\BasicCredentials;
use Dogado\JsonApi\Client\Model\OAuth2Credentials;
use Dogado\JsonApi\Client\Model\QueryCredentials;
use Dogado\JsonApi\Client\Tests\TestCase;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Support\Collection\CollectionInterface;
use Dogado\JsonApi\Support\Collection\KeyValueCollection;
use Dogado\JsonApi\Support\Collection\KeyValueCollectionInterface;
use PHPUnit\Framework\MockObject\MockObject;

class AuthenticationMiddlewareTest extends TestCase
{
    private AuthenticationMiddleware $authMiddleware;

    /** @var RequestInterface|MockObject */
    private $request;
    /** @var CollectionInterface|MockObject */
    private $headers;

    protected function setUp(): void
    {
        $this->authMiddleware = new AuthenticationMiddleware();
        $this->request = $this->createMock(RequestInterface::class);
        $this->headers = $this->createMock(KeyValueCollectionInterface::class);
    }

    public function testOAuth2(): void
    {
        $oauth2Credentials = new OAuth2Credentials(
            $this->faker()->slug(),
            $this->faker()->md5(),
            $this->faker()->dateTime()
        );

        $this->authMiddleware->setOAuth2Credentials($oauth2Credentials);
        $this->headers->expects(self::once())->method('merge')->with([
            'Authorization' => $oauth2Credentials->tokenType . ' ' . $oauth2Credentials->accessToken,
        ]);
        $this->request->expects(self::atLeastOnce())->method('headers')->willReturn($this->headers);
        $this->authMiddleware->authenticateRequest($this->request);
    }

    public function testBasic(): void
    {
        $basicCredentials = new BasicCredentials(
            $this->faker()->userName(),
            $this->faker()->password(),
        );

        $this->authMiddleware->setBasicCredentials($basicCredentials);
        $this->headers->expects(self::once())->method('merge')->with([
            'Authorization' => 'Basic ' . base64_encode(
                $basicCredentials->username . ':' . $basicCredentials->password
            ),
        ]);
        $this->request->expects(self::atLeastOnce())->method('headers')->willReturn($this->headers);
        $this->authMiddleware->authenticateRequest($this->request);
    }

    public function testQuery(): void
    {
        $queryCredentials = new QueryCredentials();
        $queryKey = $this->faker()->word();
        $queryValue = $this->faker()->word();
        $queryCredentials->set($queryKey, $queryValue);

        $this->authMiddleware->setQueryCredentials($queryCredentials);

        $requestQuery = $this->createMock(KeyValueCollection::class);
        $requestQuery->expects(self::once())->method('mergeCollection')->with($queryCredentials);
        $this->request->expects(self::once())->method('customQueryParameters')->willReturnReference($requestQuery);

        $this->authMiddleware->authenticateRequest($this->request);
    }
}
