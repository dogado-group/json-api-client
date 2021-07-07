<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Action;

use Dogado\JsonApi\Client\Factory\RequestFactoryInterface;
use Dogado\JsonApi\Client\JsonApiClient;
use Dogado\JsonApi\Client\Validator\ResponseValidator;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Throwable;

abstract class AbstractAction implements ActionInterface
{
    /** @var callable[] */
    protected array $preExecutionCallStack = [];

    public function __construct(
        protected JsonApiClient $client,
        protected RequestFactoryInterface $requestFactory,
        protected UriFactoryInterface $uriFactory,
        protected ResponseValidator $responseValidator
    ) {
    }

    abstract public function execute(): ResponseInterface;

    protected function beforeSend(string $id, callable $function): self
    {
        if (!array_key_exists($id, $this->preExecutionCallStack)) {
            $this->preExecutionCallStack[$id] = $function;
        }

        return $this;
    }

    /**
     * @throws Throwable
     */
    protected function send(RequestInterface $request): ResponseInterface
    {
        foreach ($this->preExecutionCallStack as $method) {
            $method($request);
        }

        return $this->client->execute($request);
    }
}
