<?php

namespace Symfony\Component\TypeInfo\Type;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * Interface implemented by all types.
 *
 * @template TPrimitive of TypeIdentifier
 * @template TVariables of Type
 */
interface TypeInterface
{
    /**
     * The primitive type that this type can be reduced to.
     *
     * @return TPrimitive
     */
    public function getTypeIdentifier(): TypeIdentifier;
    public function getName(): string;

    public function isNullable(): bool;
    public function asNonNullable(): self;

    public function accepts(Type $type): bool;
    public function acceptsValue(mixed $value): bool;

    /**
     * @param callable(Type): bool $callable
     */
    public function is(callable $callable): bool;
    public function isA(Type $type): bool;
}
