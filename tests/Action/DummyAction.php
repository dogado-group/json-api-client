<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Tests\Action;

use Dogado\JsonApi\Client\Action\AbstractAction;
use Dogado\JsonApi\Client\Action\FiltersResource;
use Dogado\JsonApi\Client\Action\PaginatesResource;
use Dogado\JsonApi\Client\Action\SortsResource;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Throwable;

class DummyAction extends AbstractAction
{
    use FiltersResource;
    use PaginatesResource;
    use SortsResource;

    /**
     * @throws Throwable
     */
    public function execute(): ResponseInterface
    {
        $request = $this->requestFactory->createGetRequest($this->uriFactory->createUri('v1/resource'), 'resource');
        $response = $this->send($request);
        $this->responseValidator->assertResourcesMatchTypeAndContainIds($response, 'resource');

        return $response;
    }
}
