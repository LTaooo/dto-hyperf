<?php

declare(strict_types=1);

namespace Ltaooo\DtoHyperf;

use Hyperf\Contract\LengthAwarePaginatorInterface;

class PageResponse extends BaseResponse
{
    public int $page;

    public int $per_page;

    public int $total;

    public array $data;

    public static function pageCollect(LengthAwarePaginatorInterface $paginator, string $className): static
    {
        $param = ['page' => $paginator->currentPage(), 'perPage' => $paginator->perPage(), 'total' => $paginator->total()];
        $param['data'] = [];
        foreach ($paginator->items() as $item) {
            $param['data'][] = new $className($item);
        }
        return PageResponse::from($param);
    }

    public function pluck(string $column): array
    {
        $result = [];
        foreach ($this->data as $item) {
            $result[] = $item->{$column};
        }
        return $result;
    }
}
