<?php

namespace Dogado\JsonApi\Client\Tests\Exception;

use Dogado\JsonApi\Client\Exception\ResponseValidationException;
use Dogado\JsonApi\Client\Tests\TestCase;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Generator;

class ResponseValidationExceptionTest extends TestCase
{
    /**
     * @dataProvider provideScenarios
     */
    public function test(ResponseValidationException $exception, int $expectedCode): void
    {
        $this->assertEquals($expectedCode, $exception->getCode());
        $this->assertInstanceOf(ResponseInterface::class, $exception->getResponse());
    }

    public function provideScenarios(): Generator
    {
        $faker = $this->faker();
        $response = $this->createMock(ResponseInterface::class);
        yield [
            ResponseValidationException::documentMissing($response),
            ResponseValidationException::CODE_DOCUMENT_MISSING
        ];
        yield [
            ResponseValidationException::documentGiven($response),
            ResponseValidationException::CODE_DOCUMENT_GIVEN
        ];
        yield [
            ResponseValidationException::resourceMissing($response),
            ResponseValidationException::CODE_RESOURCE_MISSING
        ];
        yield [
            ResponseValidationException::typeMismatch($response, $faker->slug, $faker->slug, $faker->numberBetween()),
            ResponseValidationException::CODE_TYPE_MISMATCH
        ];
        yield [
            ResponseValidationException::resourceIdEmpty($response, $faker->numberBetween()),
            ResponseValidationException::CODE_RESOURCE_ID_EMPTY
        ];
        yield [
            ResponseValidationException::scalarResultExpected($response, $faker->numberBetween()),
            ResponseValidationException::CODE_SCALAR_RESULT_EXPECTED
        ];
    }
}