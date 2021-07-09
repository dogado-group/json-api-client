<?php

namespace Dogado\JsonApi\Client\Tests\Action;

use Dogado\JsonApi\Client\JsonApiClient;
use Dogado\JsonApi\Client\Tests\TestCase;
use Dogado\JsonApi\Client\Factory\RequestFactoryInterface;
use Dogado\JsonApi\Client\Validator\ResponseValidator;
use Dogado\JsonApi\Model\Request\RequestInterface;
use Dogado\JsonApi\Model\Response\ResponseInterface;
use Dogado\JsonApi\Support\Collection\KeyValueCollectionInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class ActionTraitTest extends TestCase
{
    private JsonApiClient $client;
    private RequestFactoryInterface $requestFactory;
    private UriFactoryInterface $uriFactory;
    private ResponseValidator $responseValidator;

    private DummyAction $request;

    protected function setUp(): void
    {
        $this->client = $this->createMock(JsonApiClient::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->uriFactory = $this->createMock(UriFactoryInterface::class);
        $this->responseValidator = $this->createMock(ResponseValidator::class);

        $this->request = new DummyAction(
            $this->client,
            $this->requestFactory,
            $this->uriFactory,
            $this->responseValidator
        );
    }

    public function testExecute(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $this->uriFactory->expects(self::once())->method('createUri')->with('v1/resource')
            ->willReturn($uri);

        $request = $this->createMock(RequestInterface::class);
        $this->requestFactory->expects(self::once())->method('createGetRequest')->with($uri)
            ->willReturn($request);

        $filterKey = $this->faker()->slug();
        $filterValue = $this->faker()->word();
        if ($this->faker()->boolean()) {
            $this->request->filter($filterKey, $filterValue);
        } else {
            $this->request->filter([$filterKey => $filterValue]);
        }

        $filterCollection = $this->createMock(KeyValueCollectionInterface::class);
        $request->expects(self::once())->method('filter')->willReturn($filterCollection);
        $filterCollection->expects(self::once())->method('set')->with($filterKey, $filterValue);

        $paginationKey = $this->faker()->slug();
        $paginationValue = $this->faker()->numberBetween();
        if ($this->faker()->boolean()) {
            $this->request->pagination($paginationKey, $paginationValue);
        } else {
            $this->request->pagination([$paginationKey => $paginationValue]);
        }
        $paginationCollection = $this->createMock(KeyValueCollectionInterface::class);
        $request->expects(self::once())->method('pagination')->willReturn($paginationCollection);
        $paginationCollection->expects(self::once())->method('set')->with($paginationKey, $paginationValue);

        $sortField = $this->faker()->slug();
        $sortDirection = $this->faker()->slug();
        if ($this->faker()->boolean()) {
            $this->request->sort($sortField, $sortDirection);
        } else {
            $this->request->sort([$sortField => $sortDirection]);
        }
        $sortingCollection = $this->createMock(KeyValueCollectionInterface::class);
        $request->expects(self::once())->method('sorting')->willReturn($sortingCollection);
        $sortingCollection->expects(self::once())->method('set')->with($sortField, $sortDirection);

        $response = $this->createMock(ResponseInterface::class);
        $this->client->expects(self::once())->method('execute')->with($request)->willReturn($response);
        $this->responseValidator->expects(self::once())->method('assertResourcesMatchTypeAndContainIds')
            ->with($response, 'resource');

        $this->assertEquals($response, $this->request->execute());
    }
}
