<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Model;

use DateTimeInterface;

class OAuth2Credentials
{
    public function __construct(
        public string $tokenType,
        public string $accessToken,
        public ?DateTimeInterface $expiresAt = null
    ) {
    }

    public function isExpired(): bool
    {
        return null !== $this->expiresAt && $this->expiresAt->getTimestamp() <= time();
    }
}
