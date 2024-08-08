<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo;

use Symfony\Component\TypeInfo\Type\TypeInterface;

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @template TPrimitive of TypeIdentifier
 * @template TVariables of Type
 *
 * @experimental
 */
abstract class Type implements TypeInterface, \Stringable
{
    use TypeFactoryTrait;

    public function isNullable(): bool
    {
        return false;
    }

    public function asNonNullable(): self
    {
        return $this;
    }

    abstract public function accepts(Type $type): bool;

    final public function acceptsValue(mixed $value): bool
    {
        return $this->accepts(self::from($value));
    }

    /**
     * @param callable(Type): bool $callable
     */
    public function is(callable $callable): bool
    {
        return $callable($this);
    }

    final public function isA(Type $type): bool
    {
        return $type->accepts($this);
    }

    /**
     * @param TPrimitive $typeIdentifier
     * @param TVariables ...$variableTypes
     */
    protected function __construct(TypeIdentifier $typeIdentifier, Type ...$variableTypes)
    {
        $this->typeIdentifier = $typeIdentifier;
        $this->variableTypes = array_values($variableTypes);
    }
}
