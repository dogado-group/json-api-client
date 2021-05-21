<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Response;

use Dogado\JsonApi\Client\Exception\ResponseException;
use Dogado\JsonApi\Exception\BadResponseException;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseFactoryInterface
{
    /**
     * @throws BadResponseException
     * @throws ResponseException
     */
    public function createResponse(
        PsrRequestInterface $psrRequest,
        RequestInterface $request,
        PsrResponseInterface $psrResponse
    ): ResponseInterface;
}
