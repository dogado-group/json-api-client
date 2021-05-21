<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Action;

use Dogado\JsonApi\Model\Response\ResponseInterface;

interface ActionInterface
{
    public function execute(): ResponseInterface;
}
