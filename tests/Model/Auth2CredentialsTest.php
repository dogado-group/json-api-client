<?php

namespace Dogado\JsonApi\Client\Tests\Model;

use DateTime;
use DateTimeInterface;
use Dogado\JsonApi\Client\Model\OAuth2Credentials;
use Dogado\JsonApi\Client\Tests\TestCase;

class Auth2CredentialsTest extends TestCase
{
    public function testIsExpired(): void
    {
        $inFuture = $this->createModel(
            (new DateTime())->add(new \DateInterval('P' . $this->faker()->numberBetween(1, 10) . 'D'))
        );
        $this->assertFalse($inFuture->isExpired());

        $inFuture = $this->createModel(
            (new DateTime())->sub(new \DateInterval('PT' . $this->faker()->numberBetween(1, 10) . 'M'))
        );
        $this->assertTrue($inFuture->isExpired());

        $notDefined = $this->createModel(null);
        $this->assertFalse($notDefined->isExpired());
    }

    private function createModel(?DateTimeInterface $dateTime): OAuth2Credentials
    {
        return new OAuth2Credentials(
            $this->faker()->slug(),
            $this->faker()->md5(),
            $dateTime
        );
    }
}