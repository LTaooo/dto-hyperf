<?php
declare(strict_types=1);

use Hyperf\Context\ApplicationContext;
use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Hyperf\Validation\ValidatorFactory;

test('request', function () {
    setContainer();
    $request = RequestA::fromRequest([
        'name' => 'test'
    ]);
    expect($request->name)->toBe('test')
        ->and($request->age)->toBe(0);
});

it('throw exception', function () {
    setContainer();
    RequestA::fromRequest([
    ]);
})->throws(ValidationException::class);

test('nest', function () {
    setContainer();
    $request = RequestB::fromRequest([
        'a' => [
            'name' => 'hello'
        ]
    ]);
    expect($request->a->name)->toBe('hello');
});

test('multi type', function () {
    setContainer();
    $request = RequestC::fromRequest([
        'name' => 'hello',
        'age' => 10
    ]);
    expect($request->name)->toBe('hello');
});

function setContainer(): void
{
    $container = Mockery::mock(\Psr\Container\ContainerInterface::class);
    $translator = new Translator(new ArrayLoader(), 'en');
    $container->shouldReceive('get')->with(ValidatorFactoryInterface::class)->andReturn(new ValidatorFactory($translator));
    ApplicationContext::setContainer($container);
}

class RequestA extends \Ltaooo\DtoHyperf\BaseRequest
{
    public string $name;

    public int $age = 0;
}

class RequestB extends \Ltaooo\DtoHyperf\BaseRequest
{
    public RequestA $a;
}

class RequestC extends \Ltaooo\DtoHyperf\BaseRequest
{
    public ?string $name;

    public int|string $age;

    protected function rules(): array
    {
        return [
            'age' => ['integer']
        ];
    }
}