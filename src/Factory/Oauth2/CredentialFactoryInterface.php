<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Factory\Oauth2;

use Dogado\JsonApi\Client\Exception\Oauth2\AuthenticationException;
use Dogado\JsonApi\Client\Model\OAuth2Credentials;
use Psr\Http\Message\ResponseInterface;

interface CredentialFactoryInterface
{
    /**
     * @throws AuthenticationException
     */
    public function fromAuthorityResponse(ResponseInterface $response): OAuth2Credentials;
}
