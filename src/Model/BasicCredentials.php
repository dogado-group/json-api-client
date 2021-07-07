<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Model;

/**
 * Credentials for the HTTP basic auth.
 */
class BasicCredentials
{
    public function __construct(
        public string $username,
        public string $password
    ) {
    }
}
