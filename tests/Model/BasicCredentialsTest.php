<?php

namespace Dogado\JsonApi\Client\Tests\Model;

use Dogado\JsonApi\Client\Model\BasicCredentials;
use Dogado\JsonApi\Client\Tests\TestCase;

class BasicCredentialsTest extends TestCase
{
    public function test(): void
    {
        $username = $this->faker()->userName();
        $password = $this->faker()->password();

        $credentials = new BasicCredentials($username, $password);
        $this->assertEquals($username, $credentials->username);
        $this->assertEquals($password, $credentials->password);
    }
}