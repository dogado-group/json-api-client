<?php

namespace Dogado\JsonApi\Client\Tests\Factory;

use Dogado\JsonApi\Client\Factory\RequestFactory;
use Dogado\JsonApi\Client\Tests\TestCase;
use Dogado\JsonApi\Model\Document\DocumentInterface;
use GuzzleHttp\Psr7\Uri;

class RequestFactoryTest extends TestCase
{
    private string $baseUrl;
    private RequestFactory $requestFactory;

    protected function setUp(): void
    {
        $this->baseUrl = 'http://' . $this->faker()->domainName() . '/' . $this->faker()->slug();
        $this->requestFactory = new RequestFactory(new Uri($this->baseUrl));
    }

    public function providesRequestData(): array
    {
        /*
         * [
         *     [<method params>],
         *     [<expected values>]
         * ]
         */
        $type = $this->faker()->word();
        $relationshipType = $this->faker()->word();
        $id = $this->faker()->randomNumber(5);
        return [
            [ #0
                ['uriPath' => "$type/$id", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => false, 'relationshipType' => null]
            ], [ #1
                ['uriPath' => "/$type/$id", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => false, 'relationshipType' => null]
            ], [ #2
                ['uriPath' => "/$type/$id/", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => false, 'relationshipType' => null]
            ], [ #3
                ['uriPath' => "prefix/$type/$id", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => false, 'relationshipType' => null]
            ], [ #4
                ['uriPath' => "/prefix/$type/$id", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => false, 'relationshipType' => null]
            ], [ #5
                ['uriPath' => "/prefix/$type/$id/", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => false, 'relationshipType' => null]
            ], [ #6
                ['uriPath' => "$type/$id/$relationshipType", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #7
                ['uriPath' => "/$type/$id/$relationshipType", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #8
                ['uriPath' => "/$type/$id/$relationshipType/", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #9
                ['uriPath' => "prefix/$type/$id/$relationshipType", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #10
                ['uriPath' => "/prefix/$type/$id/$relationshipType", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #11
                ['uriPath' => "/prefix/$type/$id/$relationshipType/", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #12
                ['uriPath' => "$type/$id/relationships/$relationshipType", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #13
                ['uriPath' => "/$type/$id/relationships/$relationshipType", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #14
                ['uriPath' => "/$type/$id/relationships/$relationshipType/", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #15
                ['uriPath' => "prefix/$type/$id/relationships/$relationshipType", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #16
                ['uriPath' => "/prefix/$type/$id/relationships/$relationshipType", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ], [ #17
                ['uriPath' => "/prefix/$type/$id/relationships/$relationshipType/", 'resourceType' => $type],
                ['type' => $type, 'id' => $id, 'relationships' => true, 'relationshipType' => $relationshipType]
            ],
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $expected
     * @dataProvider providesRequestData
     */
    public function testCreateGetRequest(array $params, array $expected): void
    {
        $request = $this->requestFactory->createGetRequest(
            new Uri($params['uriPath']),
            $params['resourceType']
        );
        $this->assertEquals('GET', $request->method());
        $this->assertEquals($expected['type'], $request->type(), 'Resource type mismatch');
        $this->assertEquals($expected['id'], $request->id(), 'Resource id mismatch');
        $this->assertEquals($expected['relationshipType'], $request->relationship());
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $expected
     * @dataProvider providesRequestData
     */
    public function testCreatePostRequest(array $params, array $expected): void
    {
        $document = $this->createMock(DocumentInterface::class);
        $request = $this->requestFactory->createPostRequest(
            new Uri($params['uriPath']),
            $params['resourceType'],
            $document
        );

        $this->assertEquals('POST', $request->method());
        $this->assertEquals($expected['type'], $request->type(), 'Resource type mismatch');
        $this->assertEquals($expected['id'], $request->id(), 'Resource id mismatch');
        $this->assertEquals($expected['relationshipType'], $request->relationship());
        $this->assertEquals($document, $request->document());
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $expected
     * @dataProvider providesRequestData
     */
    public function testCreatePatchRequest(array $params, array $expected): void
    {
        $document = $this->createMock(DocumentInterface::class);
        $request = $this->requestFactory->createPatchRequest(
            new Uri($params['uriPath']),
            $params['resourceType'],
            $document
        );

        $this->assertEquals('PATCH', $request->method());
        $this->assertEquals($expected['type'], $request->type(), 'Resource type mismatch');
        $this->assertEquals($expected['id'], $request->id(), 'Resource id mismatch');
        $this->assertEquals($expected['relationshipType'], $request->relationship());
        $this->assertEquals($document, $request->document());
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $expected
     * @dataProvider providesRequestData
     */
    public function testCreateDeleteRequest(array $params, array $expected): void
    {
        $document = $this->createMock(DocumentInterface::class);
        $request = $this->requestFactory->createDeleteRequest(
            new Uri($params['uriPath']),
            $params['resourceType'],
            $document
        );

        $this->assertEquals('DELETE', $request->method());
        $this->assertEquals($expected['type'], $request->type(), 'Resource type mismatch');
        $this->assertEquals($expected['id'], $request->id(), 'Resource id mismatch');
        $this->assertEquals($expected['relationshipType'], $request->relationship());
        $this->assertEquals($document, $request->document());

        $request = $this->requestFactory->createDeleteRequest(
            new Uri($params['uriPath']),
            $params['resourceType']
        );
        $this->assertEquals(null, $request->document());
    }

    public function testWithUserInfo(): void
    {
        $this->baseUrl = 'http://' . $this->faker()->domainName() . '/' . $this->faker()->slug() . '?foo2=bar2';
        $this->requestFactory = new RequestFactory(new Uri($this->baseUrl));

        $user = $this->faker()->userName();
        $pass = $this->faker()->userName();

        $type = $this->faker()->slug();
        $id = (string) $this->faker()->numberBetween();
        $uri = sprintf('http://%s:%s@%s/%s/%s?foo=bar', $user, $pass, $this->faker()->domainName(), $type, $id);
        $request = $this->requestFactory->createGetRequest(new Uri($uri), $type);
        $this->assertEquals($type, $request->type());
        $this->assertEquals($id, $request->id());
        $this->assertEquals($request->uri()->getUserInfo(), $user . ':' . $pass);
    }
}
