<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Middleware;

use Dogado\JsonApi\Model\Request\RequestInterface;

interface AuthenticationMiddlewareInterface
{
    public function authenticateRequest(RequestInterface $request): void;
}
