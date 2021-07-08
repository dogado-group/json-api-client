<?php

namespace Dogado\JsonApi\Client\Tests\Response;

use Dogado\JsonApi\Client\Exception\ResponseException;
use Dogado\JsonApi\Client\Response\Response;
use Dogado\JsonApi\Client\Response\ResponseFactory;
use Dogado\JsonApi\Client\Tests\TestCase;
use Dogado\JsonApi\Exception\BadResponseException;
use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Serializer\Deserializer;
use Dogado\JsonApi\Support\Collection\KeyValueCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class ResponseFactoryTest extends TestCase
{
    /** @var MockObject */
    private $deserializer = null;

    protected function setUp(): void
    {
        $this->deserializer = $this->createMock(Deserializer::class);
    }

    public function testCreateResponse(): void
    {
        $psrRequest = $this->createMock(PsrRequestInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $psrResponse = $this->createMock(PsrResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $psrResponse->expects(self::exactly(2))->method('getBody')->willReturn($stream);

        $responseBody = [$this->faker()->text()];
        $responseBodyEncoded = json_encode($responseBody);
        $stream->expects(self::once())->method('getContents')->willReturn($responseBodyEncoded);
        $stream->expects(self::once())->method('rewind');

        $httpStatus = 200;
        $psrResponse->method('getStatusCode')->willReturn($httpStatus);
        $headers = [
            $this->faker()->word() => $this->faker()->word(),
        ];
        $psrResponse->method('getHeaders')->willReturn($headers);

        $document = $this->createMock(DocumentInterface::class);
        $deserializer = $this->deserializer;
        $deserializer->expects(self::once())->method('deserializeDocument')
            ->with($responseBody)->willReturn($document);

        $expected = new Response(
            $psrResponse,
            $document
        );
        $response = (new ResponseFactory($deserializer))->createResponse(
            $psrRequest,
            $request,
            $psrResponse
        );
        /** @var Deserializer $deserializer */
        $this->assertEquals($expected, $response);
        $this->assertEquals($httpStatus, $response->status());
        $this->assertEquals(new KeyValueCollection($headers), $response->headers());
        $this->assertEquals($psrResponse, $response->psrResponse());
        $this->assertEquals($document, $response->document());
    }

    public function testCreateResponseThrowsInvalidJson(): void
    {
        $psrRequest = $this->createMock(PsrRequestInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $psrResponse = $this->createMock(PsrResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $psrResponse->expects(self::exactly(2))->method('getBody')->willReturn($stream);

        $responseBodyEncoded = utf8_decode('äöü');
        $stream->expects(self::once())->method('getContents')->willReturn($responseBodyEncoded);
        $stream->expects(self::once())->method('rewind');

        $httpStatus = 200;
        $psrResponse->method('getStatusCode')->willReturn($httpStatus);

        $this->expectException(BadResponseException::class);
        (new ResponseFactory($this->deserializer))->createResponse(
            $psrRequest,
            $request,
            $psrResponse
        );
    }

    public function testCreateErrorResponseThrowsNoInvalidJson(): void
    {
        $psrRequest = $this->createMock(PsrRequestInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $psrResponse = $this->createMock(PsrResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $psrResponse->expects(self::exactly(2))->method('getBody')->willReturn($stream);

        $headers = [
            $this->faker()->word() => $this->faker()->word(),
        ];
        $psrResponse->method('getHeaders')->willReturn($headers);

        $responseBodyEncoded = utf8_decode('äöü');
        $stream->expects(self::once())->method('getContents')->willReturn($responseBodyEncoded);
        $stream->expects(self::once())->method('rewind');

        $httpStatus = $this->faker()->numberBetween(400, 599);
        $psrResponse->method('getStatusCode')->willReturn($httpStatus);

        try {
            (new ResponseFactory($this->deserializer))->createResponse(
                $psrRequest,
                $request,
                $psrResponse
            );
        } catch (ResponseException $e) {
            $this->assertEquals(ResponseException::CODE_UNSUCCESSFUL_HTTP_STATUS, $e->getCode());
            $this->assertEquals($httpStatus, $e->psrResponse()->getStatusCode());
            $this->assertEquals($httpStatus, $e->response()->status());
            $this->assertNull($e->response()->document());

            return;
        }

        throw new RuntimeException('The response factory threw no ResponseException');
    }

    public function testCreateErrorResponseContainsValidJsonResponse(): void
    {
        $psrRequest = $this->createMock(PsrRequestInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $psrResponse = $this->createMock(PsrResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $psrResponse->expects(self::exactly(2))->method('getBody')->willReturn($stream);

        $headers = [
            $this->faker()->word() => $this->faker()->word(),
        ];
        $psrResponse->method('getHeaders')->willReturn($headers);

        $responseBody = [$this->faker()->text()];
        $responseBodyEncoded = json_encode($responseBody);
        $stream->expects(self::once())->method('getContents')->willReturn($responseBodyEncoded);
        $stream->expects(self::once())->method('rewind');

        $httpStatus = $this->faker()->numberBetween(400, 599);
        $psrResponse->method('getStatusCode')->willReturn($httpStatus);

        $document = $this->createMock(DocumentInterface::class);
        $this->deserializer->expects(self::once())->method('deserializeDocument')
            ->with($responseBody)->willReturn($document);

        try {
            (new ResponseFactory($this->deserializer))->createResponse(
                $psrRequest,
                $request,
                $psrResponse
            );
        } catch (ResponseException $e) {
            $this->assertEquals(ResponseException::CODE_UNSUCCESSFUL_HTTP_STATUS, $e->getCode());
            $this->assertEquals($httpStatus, $e->psrResponse()->getStatusCode());
            $this->assertEquals($document, $e->response()->document());

            return;
        }

        throw new RuntimeException('The response factory threw no ResponseException');
    }
}
