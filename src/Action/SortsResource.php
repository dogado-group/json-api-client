<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Action;

use Dogado\JsonApi\Model\Request\RequestInterface;

trait SortsResource
{
    protected array $sort = [];

    /**
     * @param string|array $sortOrKey
     * @param mixed $direction
     * @return $this
     */
    public function sort($sortOrKey, $direction = null): self
    {
        $this->beforeSend('sort', function (RequestInterface $request) {
            $this->applySorting($request);
        });
        if (is_array($sortOrKey)) {
            $this->sort = array_merge($this->sort, $sortOrKey);
            return $this;
        }

        $this->sort[$sortOrKey] = $direction;
        return $this;
    }

    protected function applySorting(RequestInterface $request): self
    {
        foreach ($this->sort as $key => $value) {
            $request->sorting()->set($key, $value);
        }

        return $this;
    }
}
