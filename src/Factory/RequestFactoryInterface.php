<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Factory;

use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Psr\Http\Message\UriInterface;

interface RequestFactoryInterface
{
    public function createGetRequest(UriInterface $path, string $resourceType): RequestInterface;

    public function createPostRequest(
        UriInterface $path,
        string $resourceType,
        DocumentInterface $body
    ): RequestInterface;

    public function createPatchRequest(
        UriInterface $path,
        string $resourceType,
        DocumentInterface $body
    ): RequestInterface;

    public function createDeleteRequest(
        UriInterface $path,
        string $resourceType,
        ?DocumentInterface $body = null
    ): RequestInterface;
}
