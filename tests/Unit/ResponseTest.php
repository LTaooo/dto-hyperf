<?php
declare(strict_types=1);

use Ltaooo\DtoHyperf\BaseResponse;
use function Hyperf\Collection\collect;

test('response', function () {
    $b = ResponseA::from(['id' => 1]);
    expect($b->id)->toBe(1);

    $b = ResponseA::collection(collect([['id' => 1], ['id' => 2]]));
    expect($b[0]->id)->toBe(1)
        ->and($b[1]->id)->toBe(2);
});

class ResponseA extends BaseResponse
{
    public int $id;
}