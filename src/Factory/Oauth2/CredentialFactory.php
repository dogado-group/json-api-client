<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Factory\Oauth2;

use DateInterval;
use DateTime;
use Dogado\JsonApi\Client\Exception\Oauth2\AuthenticationException;
use Dogado\JsonApi\Client\Model\OAuth2Credentials;
use Exception;
use Psr\Http\Message\ResponseInterface;

class CredentialFactory implements CredentialFactoryInterface
{
    /**
     * @throws AuthenticationException
     */
    public function fromAuthorityResponse(ResponseInterface $response): OAuth2Credentials
    {
        list($tokenType, $accessToken, $expiresIn) = $this->parseResponse($response);

        try {
            $expiresAt = (new DateTime())->add(new DateInterval('PT' . ((int) $expiresIn - 5) . 'S'));
        } catch (Exception $e) {
            throw AuthenticationException::unableToDetermineExpiration((int) $expiresIn);
        }

        return new OAuth2Credentials($tokenType, $accessToken, $expiresAt);
    }

    /**
     * @throws AuthenticationException
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $responseBody = $response->getBody()->getContents();
        $response->getBody()->rewind();
        if (empty($responseBody)) {
            throw AuthenticationException::unableToDecodeResponse()->setResponse($response);
        }

        $data = json_decode($responseBody, true);
        if (empty($data) || !is_array($data)) {
            throw AuthenticationException::unableToDecodeResponse()->setResponse($response);
        }

        $this->checkForErrors($data, $response);
        return $this->parseData($data);
    }

    /**
     * @throws AuthenticationException
     */
    private function checkForErrors(array $data, ResponseInterface $response): void
    {
        if (isset($data['error'])) {
            $errorSlug = $data['error'];
            throw AuthenticationException::failed($errorSlug)->setResponse($response);
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws AuthenticationException
     */
    private function parseData(array $data): array
    {
        $missing = [];
        $tokenType = $data['token_type'] ?? null;
        if (empty($tokenType)) {
            $missing[] = 'token_type';
        }
        $accessToken = $data['access_token'] ?? null;
        if (empty($accessToken)) {
            $missing[] = 'access_token';
        }
        $expiresIn = $data['expires_in'] ?? null;
        if (empty($expiresIn)) {
            $missing[] = 'expires_in';
        }

        if (!empty($missing)) {
            throw AuthenticationException::missingDataInResponse($missing);
        }

        return [
            $tokenType,
            $accessToken,
            $expiresIn,
        ];
    }
}
