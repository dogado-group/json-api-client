<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Factory;

use Dogado\JsonApi\Exception\JsonApi\BadRequestException;
use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Request\Request;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface
{
    protected UriInterface $baseUrl;

    public function __construct(UriInterface $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @throws BadRequestException
     */
    public function createGetRequest(UriInterface $path, string $resourceType): RequestInterface
    {
        return $this->createRequest('GET', $path, $resourceType);
    }

    /**
     * @throws BadRequestException
     */
    public function createPostRequest(
        UriInterface $path,
        string $resourceType,
        DocumentInterface $body
    ): RequestInterface {
        return $this->createRequest('POST', $path, $resourceType, $body);
    }

    /**
     * @throws BadRequestException
     */
    public function createPatchRequest(
        UriInterface $path,
        string $resourceType,
        DocumentInterface $body
    ): RequestInterface {
        return $this->createRequest('PATCH', $path, $resourceType, $body);
    }

    /**
     * @throws BadRequestException
     */
    public function createDeleteRequest(
        UriInterface $path,
        string $resourceType,
        ?DocumentInterface $body = null
    ): RequestInterface {
        return $this->createRequest('DELETE', $path, $resourceType, $body);
    }

    /**
     * @throws BadRequestException
     */
    protected function createRequest(
        string $httpMethod,
        UriInterface $path,
        string $resourceType,
        ?DocumentInterface $body = null
    ): RequestInterface {
        $uri = $this->baseUrl;

        if ('' !== $path->getHost()) {
            $uri = $uri->withScheme($path->getScheme())
                ->withHost($path->getHost())
                ->withPort($path->getPort());

            if ('' !== $path->getUserInfo()) {
                $parts = explode(':', $path->getUserInfo(), 2);
                $password = null;
                if (count($parts) === 2) {
                    $password = $parts[1];
                }
                $uri = $uri->withUserInfo($parts[0], $password);
            }
        }

        if ('' !== $path->getQuery()) {
            if ('' === $uri->getQuery()) {
                $uri = $uri->withQuery($path->getQuery());
            } else {
                $pathQuery = [];
                parse_str($path->getQuery(), $pathQuery);
                $uriQuery = [];
                parse_str($uri->getQuery(), $uriQuery);
                $uri->withQuery(http_build_query(array_merge($uriQuery, $pathQuery)));
            }
        }

        /* The prefix before any restful uri paths (e.g `v1`) */
        $apiBasePrefix = null;
        /* The prefix before the requested resource type (e.g `user/12345/`) */
        $pathPrefix = trim(Str::before($path->getPath(), $resourceType), '/');
        if ('' !== $uri->getPath()) {
            $apiBasePrefix = trim($uri->getPath(), '/');
            if (!empty($apiBasePrefix)) {
                $apiBasePrefix .= '/';
            }
        }

        $uri = $uri->withPath('/' . $apiBasePrefix . trim($path->getPath(), '/'));
        $prefix = $apiBasePrefix;
        if ($path->getPath() !== $pathPrefix) {
            $prefix .= $pathPrefix . '/';
        }

        return new Request($httpMethod, $uri, $body, $prefix);
    }
}
