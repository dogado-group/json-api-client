<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Exception;

use Dogado\JsonApi\Model\Response\ResponseInterface;
use Exception;
use Throwable;

class ResponseValidationException extends Exception
{
    public const CODE_DOCUMENT_MISSING = 100;
    public const CODE_DOCUMENT_GIVEN = 101;
    public const CODE_RESOURCE_MISSING = 102;
    public const CODE_TYPE_MISMATCH = 103;
    public const CODE_RESOURCE_ID_EMPTY = 104;
    public const CODE_SCALAR_RESULT_EXPECTED = 105;

    protected ResponseInterface $response;

    public static function documentMissing(ResponseInterface $response): self
    {
        return new self(
            'JSON API response contains no document',
            self::CODE_DOCUMENT_MISSING,
            $response
        );
    }

    public static function documentGiven(ResponseInterface $response): self
    {
        return new self(
            'JSON API response contains document although it\'s not expected',
            self::CODE_DOCUMENT_GIVEN,
            $response
        );
    }

    public static function resourceMissing(ResponseInterface $response): self
    {
        return new self(
            'JSON API response contains no document resources, at least one expected',
            self::CODE_RESOURCE_MISSING,
            $response
        );
    }

    public static function typeMismatch(
        ResponseInterface $response,
        string $expected,
        string $actual,
        int $resourceItem
    ): self {
        return new self(
            sprintf(
                'JSON API response contains resources with unexpected type: "%s" expected, got "%s" for resource #%d',
                $expected,
                $actual,
                $resourceItem
            ),
            self::CODE_TYPE_MISMATCH,
            $response
        );
    }

    public static function resourceIdEmpty(
        ResponseInterface $response,
        int $resourceItem
    ): self {
        return new self(
            sprintf(
                'JSON API response contains resources with empty IDs (resource #%d)',
                $resourceItem
            ),
            self::CODE_RESOURCE_ID_EMPTY,
            $response
        );
    }

    public static function scalarResultExpected(ResponseInterface $response, int $totalResources): self
    {
        return new self(
            sprintf(
                'JSON API response contains %s, 1 expected',
                0 === $totalResources ? 'no resource' : $totalResources . ' resources'
            ),
            self::CODE_SCALAR_RESULT_EXPECTED,
            $response
        );
    }

    public function __construct(string $message, int $code, ResponseInterface $response, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
