<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Response;

use Dogado\JsonApi\Model\Document\DocumentInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Dogado\JsonApi\Support\Collection\KeyValueCollection;
use Dogado\JsonApi\Support\Collection\KeyValueCollectionInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response implements ResponseInterface
{
    private int $status;
    private KeyValueCollectionInterface $headers;

    public function __construct(
        protected PsrResponseInterface $psrResponse,
        private ?DocumentInterface $document = null
    ) {
        $this->status = $psrResponse->getStatusCode();
        $this->headers = new KeyValueCollection();
        foreach ($psrResponse->getHeaders() as $header => $value) {
            if (is_array($value) && count($value) === 1) {
                $value = $value[0];
            }
            $this->headers->set($header, $value);
        }
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): KeyValueCollectionInterface
    {
        return $this->headers;
    }

    public function document(): ?DocumentInterface
    {
        return $this->document;
    }

    public function psrResponse(): ?PsrResponseInterface
    {
        return $this->psrResponse;
    }
}
