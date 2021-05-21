<?php

namespace Dogado\JsonApi\Client\Tests;

use Dogado\JsonApi\Client\JsonApiClient;
use Dogado\JsonApi\Client\Middleware\AuthenticationMiddlewareInterface;
use Dogado\JsonApi\Client\Response\ResponseFactoryInterface;
use Dogado\JsonApi\Exception\JsonApi\BadRequestException;
use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Serializer\DocumentSerializerInterface;
use Dogado\JsonApi\Support\Collection\KeyValueCollection;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class JsonApiClientTest extends TestCase
{
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private DocumentSerializerInterface $serializer;
    private ResponseFactoryInterface $responseFactory;
    private AuthenticationMiddlewareInterface $authMiddleware;

    private JsonApiClient $client;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->serializer = $this->createMock(DocumentSerializerInterface::class);
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->authMiddleware = $this->createMock(AuthenticationMiddlewareInterface::class);

        $this->client = new JsonApiClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->serializer,
            $this->responseFactory,
            $this->authMiddleware
        );
    }

    public function testExecute(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $method = $this->faker()->slug;

        $request->expects(self::once())->method('method')->willReturn($method);
        $uri = $this->createMock(UriInterface::class);
        $request->expects(self::once())->method('uri')->willReturn($uri);

        $this->authMiddleware->expects(self::once())->method('authenticateRequest')->with($request);

        $httpRequest = $this->createMock(\Psr\Http\Message\RequestInterface::class);
        $this->requestFactory->method('createRequest')->with($method, $uri)->willReturn($httpRequest);

        $headerKey = $this->faker()->slug;
        $headerValue = $this->faker()->slug;
        $header = new KeyValueCollection([$headerKey => $headerValue]);
        $request->expects(self::once())->method('headers')->willReturn($header);

        $httpRequest->expects(self::once())->method('withHeader')->with($headerKey, $headerValue)->willReturnSelf();

        $document = $this->createMock(DocumentInterface::class);
        $request->expects(self::atLeastOnce())->method('document')->willReturn($document);

        $data = [$this->faker()->text];
        $this->serializer->method('serializeDocument')->with($document)->willReturn($data);

        $stream = $this->createMock(StreamInterface::class);
        $this->streamFactory->expects(self::once())->method('createStream')->with(json_encode($data))
            ->willReturn($stream);

        $httpRequest->expects(self::once())->method('withBody')->with($stream)->willReturnSelf();

        $httpResponse = $this->createMock(ResponseInterface::class);
        $this->httpClient->expects(self::once())->method('sendRequest')->with($httpRequest)->willReturn($httpResponse);

        $httpRequest->expects(self::once())->method('getBody')->willReturn($stream);
        $stream->expects(self::once())->method('rewind');

        $response = $this->createMock(\Dogado\JsonApi\Model\Response\ResponseInterface::class);
        $this->responseFactory->expects(self::once())->method('createResponse')->with(
                $httpRequest,
                $request,
                $httpResponse
            )
            ->willReturn($response);

        $this->assertEquals($response, $this->client->execute($request));
    }

    public function testFailedSerialization(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $method = $this->faker()->slug;

        $request->expects(self::once())->method('method')->willReturn($method);
        $uri = $this->createMock(UriInterface::class);
        $request->expects(self::once())->method('uri')->willReturn($uri);

        $httpRequest = $this->createMock(\Psr\Http\Message\RequestInterface::class);
        $this->requestFactory->method('createRequest')->with($method, $uri)->willReturn($httpRequest);

        $headerKey = $this->faker()->slug;
        $headerValue = $this->faker()->slug;
        $header = new KeyValueCollection([$headerKey => $headerValue]);
        $request->expects(self::once())->method('headers')->willReturn($header);

        $httpRequest->expects(self::once())->method('withHeader')->with($headerKey, $headerValue)->willReturnSelf();

        $document = $this->createMock(DocumentInterface::class);
        $request->expects(self::atLeastOnce())->method('document')->willReturn($document);

        $this->serializer->method('serializeDocument')->with($document)->willReturn([utf8_decode('öäü')]);
        $this->expectExceptionObject(new BadRequestException('Unable to serialize json api request document'));
        $this->client->execute($request);
    }
}
