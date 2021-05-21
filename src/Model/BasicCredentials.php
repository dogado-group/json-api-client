<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Model;

/**
 * Credentials for the HTTP basic auth.
 */
class BasicCredentials
{
    public string $username;
    public string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}
