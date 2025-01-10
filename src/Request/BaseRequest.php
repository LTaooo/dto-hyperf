<?php
declare(strict_types=1);

namespace Ltaooo\DtoHyperf;

use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Ltaooo\Data\Data;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionNamedType;
use ReflectionType;

class BaseRequest extends Data
{
    /**
     * @throws
     */
    public static function fromRequest(array|RequestInterface $request): static
    {
        $instance = new static();
        $result = $instance->validate($request instanceof RequestInterface ? array_merge($request->all(), $request->getUploadedFiles()) : $request);
        $instance->fill($result);
        return $instance;
    }

    protected function rules(): array
    {
        return [];
    }

    protected function messages(): array
    {
        return [];
    }

    protected function attributes(): array
    {
        return [];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function validate(array $data): array
    {
        $validate = ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class)->make($data, $this->getRules(), $this->messages(), $this->attributes());
        if ($validate->fails()) {
            throw new ValidationException($validate);
        }
        return $validate->validated();
    }

    protected function getRules(): array
    {
        $methodRules = $this->rules();
        $attributeRules = [];
        foreach ($this->getStaticReflection()->getProperties() as $property) {
            if ($this->isInsideProperty($property)) {
                continue;
            }
            $snakePropertyName = Str::snake($property->getName());

            if ($property->getType()->allowsNull()) {
                $attributeRules[$snakePropertyName][] = 'nullable';
            }
            if (!$property->getType()->allowsNull() && !$property->hasDefaultValue()) {
                $attributeRules[$snakePropertyName][] = 'required';
            }
            $this->addRule($property->getType(), $snakePropertyName, $attributeRules);
            $methodRules[$snakePropertyName] = array_merge($methodRules[$snakePropertyName] ?? [], $attributeRules[$snakePropertyName] ?? []);
        }
        return $methodRules;
    }

    private function addRule(ReflectionType $type, string $snakePropertyName, array &$attributeRules): void
    {
        if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
            switch ($type->getName()) {
                case 'string':
                    $attributeRules[$snakePropertyName][] = 'string';
                    break;
                case 'int':
                    $attributeRules[$snakePropertyName][] = 'integer';
                    break;
                case 'array':
                    $attributeRules[$snakePropertyName][] = 'array';
                    break;
                case 'float':
                case 'double':
                    $attributeRules[$snakePropertyName][] = 'numeric';
            }
        }
        // if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
        //     foreach ($type->getTypes() as $subType) {
        //         $this->addRule($subType, $snakePropertyName, $attributeRules);
        //     }
        // }
    }
}