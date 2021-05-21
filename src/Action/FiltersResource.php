<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Action;

use Dogado\JsonApi\Model\Request\RequestInterface;

trait FiltersResource
{
    protected array $filter = [];

    /**
     * @param string|array $filterOrKey
     * @param mixed $value
     * @return $this
     */
    public function filter($filterOrKey, $value = null): self
    {
        $this->beforeSend('filter', function (RequestInterface $request) {
            $this->applyFilter($request);
        });
        if (is_array($filterOrKey)) {
            $this->filter = array_merge($this->filter, $filterOrKey);
            return $this;
        }

        $this->filter[$filterOrKey] = $value;
        return $this;
    }

    protected function applyFilter(RequestInterface $request): self
    {
        foreach ($this->filter as $key => $value) {
            $request->filter()->set($key, $value);
        }

        return $this;
    }
}
