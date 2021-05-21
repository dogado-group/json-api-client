<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Exception\Oauth2;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AuthenticationException extends Exception
{
    public const CODE_FAILED = 1000;
    public const CODE_JSON_DECODE_FAILED = 1001;
    public const CODE_MISSING_DATA_IN_RESPONSE = 1002;
    public const CODE_UNABLE_TO_DETERMINE_EXPIRATION = 1003;
    public const CODE_UNABLE_TO_ENCODE_PAYLOAD = 1004;

    private ?ResponseInterface $response = null;

    public static function failed(?string $errorSlug, ?Throwable $previous = null): self
    {
        if (!empty($errorSlug)) {
            $errorSlug = ": $errorSlug";
        } elseif (null !== $previous) {
            $errorSlug = ': ' . $previous->getMessage();
        }
        return new self('Authentication failed' . $errorSlug, self::CODE_FAILED, $previous);
    }

    public static function unableToDecodeResponse(): self
    {
        return new self('Unable to decode response as json string', self::CODE_JSON_DECODE_FAILED);
    }

    /**
     * @param string[] $missing
     */
    public static function missingDataInResponse(array $missing): self
    {
        return new self(
            'The authentication response is missing one or more attributes: ' . implode(', ', $missing),
            self::CODE_MISSING_DATA_IN_RESPONSE
        );
    }

    public static function unableToDetermineExpiration(int $expiresIn): self
    {
        return new self(
            'Unable to determine expiration date based on ' . $expiresIn . ' seconds',
            self::CODE_UNABLE_TO_DETERMINE_EXPIRATION
        );
    }

    public static function unableToEncodePayload(?string $jsonError): self
    {
        return new self(
            'Unable to encode authentication payload to json. Error: ' . ($jsonError ?? 'unknown'),
            self::CODE_UNABLE_TO_ENCODE_PAYLOAD
        );
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(?ResponseInterface $response): self
    {
        $this->response = $response;
        return $this;
    }
}
