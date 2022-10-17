<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Validator;

use Dogado\JsonApi\Client\Exception\ResponseValidationException;
use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;

class ResponseValidator
{
    /**
     * @throws ResponseValidationException
     */
    public function assertDocument(ResponseInterface $response): void
    {
        if (null === $response->document()) {
            throw ResponseValidationException::documentMissing($response);
        }
    }

    /**
     * @throws ResponseValidationException
     */
    public function assertNoDocument(ResponseInterface $response): void
    {
        if (null !== $response->document()) {
            throw ResponseValidationException::documentGiven($response);
        }
    }

    /**
     * @throws ResponseValidationException
     */
    public function assertDataNotEmpty(ResponseInterface $response): void
    {
        $this->assertDocument($response);

        assert($response->document() instanceof DocumentInterface);
        if ($response->document()->data()->isEmpty()) {
            throw ResponseValidationException::resourceMissing($response);
        }
    }

    /**
     * @throws ResponseValidationException
     */
    public function assertResourcesMatchTypeAndContainIds(ResponseInterface $response, string $type): void
    {
        $this->assertDocument($response);

        assert($response->document() instanceof DocumentInterface);
        foreach ($response->document()->data()->all() as $key => $resource) {
            if ($type !== $resource->type()) {
                throw ResponseValidationException::typeMismatch($response, $type, $resource->type(), $key);
            }

            if (empty($resource->id())) {
                throw ResponseValidationException::resourceIdEmpty($response, $key);
            }
        }
    }

    /**
     * @throws ResponseValidationException
     */
    public function assertResourcesMatchType(ResponseInterface $response, string $type): void
    {
        $this->assertDocument($response);

        assert($response->document() instanceof DocumentInterface);
        foreach ($response->document()->data()->all() as $key => $resource) {
            if ($type !== $resource->type()) {
                throw ResponseValidationException::typeMismatch($response, $type, $resource->type(), $key);
            }
        }
    }

    /**
     * @throws ResponseValidationException
     */
    public function assertScalarResult(ResponseInterface $response): void
    {
        assert($response->document() instanceof DocumentInterface);
        if (1 !== $response->document()->data()->count()) {
            throw ResponseValidationException::scalarResultExpected($response, $response->document()->data()->count());
        }
    }

    /**
     * @throws ResponseValidationException
     */
    public function assertScalarResultWithId(ResponseInterface $response, string $type): void
    {
        $this->assertDataNotEmpty($response);
        $this->assertResourcesMatchTypeAndContainIds($response, $type);
        $this->assertScalarResult($response);
    }

    /**
     * @throws ResponseValidationException
     */
    public function assertScalarResultWithoutId(ResponseInterface $response, string $type): void
    {
        $this->assertDataNotEmpty($response);
        $this->assertResourcesMatchType($response, $type);
        $this->assertScalarResult($response);

        if (!empty($response->document()?->data()->first()->id())) {
            throw ResponseValidationException::resourceIdFound($response);
        }
    }

    /**
     * @throws ResponseValidationException
     */
    public function assertScalarResultWithOptionalId(ResponseInterface $response, string $type): void
    {
        $this->assertDataNotEmpty($response);
        $this->assertResourcesMatchType($response, $type);
        $this->assertScalarResult($response);
    }
}
