<?php

declare(strict_types=1);

namespace Dogado\JsonApi\Client\Action;

use Dogado\JsonApi\Model\Request\RequestInterface;

trait PaginatesResource
{
    protected array $pagination = [];

    /**
     * @param string|array $paginationOrKey
     */
    public function pagination(mixed $paginationOrKey, mixed $value = null): self
    {
        $this->beforeSend('paginate', function (RequestInterface $request) {
            $this->applyPagination($request);
        });

        if (is_array($paginationOrKey)) {
            $this->pagination = array_merge($this->pagination, $paginationOrKey);
            return $this;
        }

        $this->pagination[$paginationOrKey] = $value;
        return $this;
    }

    protected function applyPagination(RequestInterface $request): self
    {
        foreach ($this->pagination as $key => $value) {
            $request->pagination()->set($key, $value);
        }

        return $this;
    }
}
