<?php

namespace Dogado\JsonApi\Client\Tests\Validator;

use Dogado\JsonApi\Client\Exception\ResponseValidationException;
use Dogado\JsonApi\Client\Response\Response;
use Dogado\JsonApi\Client\Tests\TestCase;
use Dogado\JsonApi\Client\Validator\ResponseValidator;
use Dogado\JsonApi\Model\Document\Document;
use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Resource\Resource;
use Dogado\JsonApi\Model\Response\ResponseInterface;

class ResponseValidatorTest extends TestCase
{
    private ResponseValidator $responseValidator;

    protected function setUp(): void
    {
        $this->responseValidator = new ResponseValidator();
    }

    public function testAssertDocument(): void
    {
        $this->responseValidator->assertDocument($this->createResponse(new Document([])));
        $this->assertTrue(true);
    }

    public function testAssertDocumentThrowsException(): void
    {
        $response = $this->createResponse();
        $this->expectExceptionObject(ResponseValidationException::documentMissing($response));

        $this->responseValidator->assertDocument($response);
    }

    public function testAssertNoDocument(): void
    {
        $this->responseValidator->assertNoDocument($this->createResponse());
        $this->assertTrue(true);
    }

    public function testAssertNoDocumentThrowsException(): void
    {
        $response = $this->createResponse(new Document([]));
        $this->expectExceptionObject(ResponseValidationException::documentGiven($response));

        $this->responseValidator->assertNoDocument($response);
    }

    public function testAssertDataNotEmpty(): void
    {
        $this->responseValidator->assertDataNotEmpty($this->createResponse(new Document([new Resource(
            $this->faker()->slug()
        )])));
        $this->assertTrue(true);
    }

    public function testAssertDataNotEmptyThrowsException(): void
    {
        $response = $this->createResponse(new Document([]));
        $this->expectExceptionObject(ResponseValidationException::resourceMissing($response));

        $this->responseValidator->assertDataNotEmpty($response);
    }

    public function testAssertResourcesMatchTypeAndContainIds(): void
    {
        $type = $this->faker()->slug();
        $response = $this->createResponse(new Document([new Resource(
            $type,
            (string) $this->faker()->numberBetween()
        )]));
        $this->responseValidator->assertResourcesMatchTypeAndContainIds($response, $type);
        $this->assertTrue(true);
    }

    public function testAssertResourcesMatchTypeAndContainIdsThrowsExceptionDueToType(): void
    {
        $expectedType = $this->faker()->slug();
        $actualType = $this->faker()->slug();
        $response = $this->createResponse(new Document([new Resource(
            $actualType,
            (string) $this->faker()->numberBetween()
        )]));
        $this->expectExceptionObject(
            ResponseValidationException::typeMismatch($response, $expectedType, $actualType, 0)
        );
        $this->responseValidator->assertResourcesMatchTypeAndContainIds($response, $expectedType);
    }

    public function testAssertResourcesMatchTypeAndContainIdsThrowsExceptionDueToId(): void
    {
        $expectedType = $this->faker()->slug();
        $response = $this->createResponse(new Document([new Resource(
            $expectedType
        )]));
        $this->expectExceptionObject(
            ResponseValidationException::resourceIdEmpty($response, 0)
        );
        $this->responseValidator->assertResourcesMatchTypeAndContainIds($response, $expectedType);
    }

    public function testAssertScalarResultWithId(): void
    {
        $type = $this->faker()->slug();
        $response = $this->createResponse(new Document([new Resource(
            $type,
            (string) $this->faker()->numberBetween()
        )]));
        $this->responseValidator->assertScalarResultWithId($response, $type);
        $this->assertTrue(true);
    }

    public function testAssertScalarResultWithIdThrowsException(): void
    {
        $type = $this->faker()->slug();
        $response = $this->createResponse(new Document([
            new Resource($type, (string) $this->faker()->numberBetween()),
            new Resource($type, (string) $this->faker()->numberBetween()),
        ]));

        $this->expectExceptionObject(
            ResponseValidationException::scalarResultExpected($response, 2)
        );
        $this->responseValidator->assertScalarResultWithId($response, $type);
    }

    public function testAssertResourcesMatchType(): void
    {
        $type = $this->faker()->slug();
        $response = $this->createResponse(new Document([new Resource(
            $type
        )]));
        $this->responseValidator->assertResourcesMatchType($response, $type);
        $this->assertTrue(true);
    }

    public function testAssertResourcesMatchTypeAndContainIdsThrowsException(): void
    {
        $expectedType = $this->faker()->slug();
        $actualType = $this->faker()->slug();
        $response = $this->createResponse(new Document([new Resource(
            $actualType,
            (string) $this->faker()->numberBetween()
        )]));
        $this->expectExceptionObject(
            ResponseValidationException::typeMismatch($response, $expectedType, $actualType, 0)
        );
        $this->responseValidator->assertResourcesMatchType($response, $expectedType);
    }

    private function createResponse(?DocumentInterface $document = null): ResponseInterface
    {
        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);
        return new Response($response, $document);
    }
}
