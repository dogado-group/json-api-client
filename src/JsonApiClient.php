<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client;

use Dogado\JsonApi\Client\Exception\ResponseException;
use Dogado\JsonApi\Client\Response\ResponseFactoryInterface;
use Dogado\JsonApi\Exception\BadResponseException;
use Dogado\JsonApi\Exception\JsonApi\BadRequestException;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Dogado\JsonApi\Serializer\DocumentSerializerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class JsonApiClient
{
    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;
    protected DocumentSerializerInterface $serializer;
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        DocumentSerializerInterface $serializer,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->serializer = $serializer;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws BadRequestException
     * @throws ClientExceptionInterface
     * @throws ResponseException
     * @throws BadResponseException
     */
    public function execute(RequestInterface $request): ResponseInterface
    {
        $httpRequest = $this->requestFactory->createRequest($request->method(), $request->uri());
        foreach ($request->headers()->all() as $header => $value) {
            $httpRequest = $httpRequest->withHeader($header, $value);
        }

        if (null !== $request->document()) {
            $bodyData = json_encode($this->serializer->serializeDocument($request->document()));
            if (false === $bodyData) {
                throw new BadRequestException('Unable to serialize json api request document');
            }

            $httpRequest = $httpRequest->withBody($this->streamFactory->createStream($bodyData));
        }

        $httpResponse = $this->httpClient->sendRequest($httpRequest);
        // reset the request body stream pointer in case of further usage within exceptions
        $httpRequest->getBody()->rewind();

        return $this->responseFactory->createResponse($httpRequest, $request, $httpResponse);
    }
}
