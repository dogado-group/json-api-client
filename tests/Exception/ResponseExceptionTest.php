<?php
namespace Dogado\JsonApi\Client\Tests\Exception;

use Dogado\JsonApi\Client\Exception\ResponseException;
use Dogado\JsonApi\Client\Tests\TestCase;
use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Dogado\JsonApi\Support\Error\ErrorCollectionInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class ResponseExceptionTest extends TestCase
{
    public function testUnsuccessfulHttpStatusReturned(): void
    {
        $expectedErrorCollection = $this->createMock(ErrorCollectionInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $psrRequest = $this->createMock(PsrRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $psrResponse = $this->createMock(PsrResponseInterface::class);

        $document = $this->createMock(DocumentInterface::class);
        $response->method('document')->willReturn($document);
        $document->method('errors')->willReturn($expectedErrorCollection);

        $exception = ResponseException::unsuccessfulHttpStatusReturned(
            $request,
            $psrRequest,
            $response,
            $psrResponse
        );

        $this->assertEquals(ResponseException::CODE_UNSUCCESSFUL_HTTP_STATUS, $exception->getCode());
        $this->assertEquals($request, $exception->request());
        $this->assertEquals($psrRequest, $exception->psrRequest());
        $this->assertEquals($response, $exception->response());
        $this->assertEquals($psrResponse, $exception->psrResponse());
        $this->assertEquals($expectedErrorCollection, $exception->errors());
    }
}