<?php
declare(strict_types=1);

namespace Ltaooo\DtoHyperf;

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Ltaooo\Data\Data;

class BaseResponse extends Data
{
    /**
     * @return array<static>
     */
    public static function collection(Collection $collection): array
    {
        $result = [];
        foreach ($collection as $item) {
            $result[] = new static($item);
        }
        return $result;
    }

    public static function page(LengthAwarePaginatorInterface $paginator): PageResponse
    {
        return PageResponse::pageCollect($paginator, static::class);
    }

    public function fill(array $data): static
    {
        $this->beforeFill($data);
        parent::fill($data);
        $this->afterFill($data);
        return $this;
    }

    public static function from($data): static
    {
        return parent::from($data);
    }
}