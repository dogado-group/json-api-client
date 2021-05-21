<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Model;

use DateTimeInterface;

class OAuth2Credentials
{
    public string $tokenType;
    public string $accessToken;
    public ?DateTimeInterface $expiresAt;

    public function __construct(string $tokenType, string $accessToken, DateTimeInterface $expiresAt = null)
    {
        $this->tokenType = $tokenType;
        $this->accessToken = $accessToken;
        $this->expiresAt = $expiresAt;
    }

    public function isExpired(): bool
    {
        return null !== $this->expiresAt && $this->expiresAt->getTimestamp() <= time();
    }
}
