<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Middleware;

use Dogado\JsonApi\Client\Model\BasicCredentials;
use Dogado\JsonApi\Client\Model\OAuth2Credentials;
use Dogado\JsonApi\Model\Request\RequestInterface;

class AuthenticationMiddleware implements AuthenticationMiddlewareInterface
{
    protected ?OAuth2Credentials $oauth2Credentials = null;
    protected ?BasicCredentials $basicCredentials = null;

    public function setOAuth2Credentials(OAuth2Credentials $oauth2Credentials): self
    {
        $this->oauth2Credentials = $oauth2Credentials;
        return $this;
    }

    public function setBasicCredentials(BasicCredentials $basicCredentials): self
    {
        $this->basicCredentials = $basicCredentials;
        return $this;
    }

    public function authenticateRequest(RequestInterface $request): void
    {
        if (null !== $this->oauth2Credentials) {
            $request->headers()->merge([
               'Authorization' => $this->oauth2Credentials->tokenType . ' ' . $this->oauth2Credentials->accessToken,
            ]);
        }

        if (null !== $this->basicCredentials) {
            $request->headers()->merge([
               'Authorization' => 'Basic ' . base64_encode(
                   $this->basicCredentials->username . ':' . $this->basicCredentials->password
               ),
            ]);
        }
    }
}
