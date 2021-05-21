<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Response;

use Dogado\JsonApi\Client\Exception\ResponseException;
use Dogado\JsonApi\Exception\BadResponseException;
use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Dogado\JsonApi\Serializer\DocumentDeserializerInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    protected DocumentDeserializerInterface $deserializer;

    public function __construct(DocumentDeserializerInterface $deserializer)
    {
        $this->deserializer = $deserializer;
    }

    /**
     * @throws BadResponseException
     * @throws ResponseException
     */
    public function createResponse(
        PsrRequestInterface $psrRequest,
        RequestInterface $request,
        PsrResponseInterface $psrResponse
    ): ResponseInterface {
        $responseBody = $psrResponse->getBody()->getContents();
        $psrResponse->getBody()->rewind();

        if ($psrResponse->getStatusCode() >= 400) {
            try {
                $responseDocument = $this->createResponseBody($responseBody);
            } catch (BadResponseException $e) {
                $responseDocument = null;
            }

            throw ResponseException::unsuccessfulHttpStatusReturned(
                $request,
                $psrRequest,
                new Response($psrResponse, $responseDocument),
                $psrResponse
            );
        }

        return new Response($psrResponse, $this->createResponseBody($responseBody));
    }

    /**
     * @throws BadResponseException
     */
    private function createResponseBody(?string $responseBody): ?DocumentInterface
    {
        if (empty($responseBody)) {
            return null;
        }

        $documentData = json_decode($responseBody, true);
        if (!is_array($documentData)) {
            throw BadResponseException::invalidJsonDocument(json_last_error_msg());
        }

        return $this->deserializer->deserializeDocument($documentData);
    }
}
