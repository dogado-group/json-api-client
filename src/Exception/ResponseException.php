<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Exception;

use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Dogado\JsonApi\Support\Error\ErrorCollection;
use Dogado\JsonApi\Support\Error\ErrorCollectionInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Exception;
use Throwable;

class ResponseException extends Exception
{
    public const CODE_UNSUCCESSFUL_HTTP_STATUS = 100;

    public static function unsuccessfulHttpStatusReturned(
        RequestInterface $request,
        PsrRequestInterface $psrRequest,
        ResponseInterface $response,
        PsrResponseInterface $psrResponse
    ): self {
        return new self(
            'Unsuccessful http status returned (' . $response->status() . ').',
            self::CODE_UNSUCCESSFUL_HTTP_STATUS,
            $request,
            $psrRequest,
            $response,
            $psrResponse
        );
    }

    public function __construct(
        string $message,
        int $code,
        protected RequestInterface $request,
        protected PsrRequestInterface $psrRequest,
        protected ResponseInterface $response,
        protected PsrResponseInterface $psrResponse,
        Throwable $previous = null
    ) {
        $document = $response->document();
        if ($document && !$document->errors()->isEmpty()) {
            foreach ($document->errors()->all() as $error) {
                $message .= '\n' . $error->title();
            }
        }

        parent::__construct($message, $code, $previous);
    }

    public function request(): RequestInterface
    {
        return $this->request;
    }

    public function psrRequest(): PsrRequestInterface
    {
        return $this->psrRequest;
    }

    public function response(): ResponseInterface
    {
        return $this->response;
    }

    public function errors(): ErrorCollectionInterface
    {
        $document = $this->response->document();
        if (null === $document) {
            return new ErrorCollection();
        }

        return $document->errors();
    }

    public function psrResponse(): PsrResponseInterface
    {
        return $this->psrResponse;
    }
}
