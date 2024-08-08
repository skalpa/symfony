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

use Symfony\Component\TypeInfo\Exception\LogicException;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\ObjectType;

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @experimental
 */
abstract class Type implements \Stringable
{
    use TypeFactoryTrait;

    abstract public function getBaseType(): BuiltinType|ObjectType;

    /**
     * Return the simplest primitive type that this type will satisfy.
     */
    abstract public function getTypeIdentifier(): TypeIdentifier;

    /**
     * @param TypeIdentifier|class-string $subject
     */
    abstract public function isA(TypeIdentifier|string $subject): bool;

    /**
     * @param callable(Type): bool $callable
     */
    public function is(callable $callable): bool
    {
        return $callable($this);
    }

    public function asNonNullable(): self
    {
        return $this;
    }

    public function isNullable(): bool
    {
        return false;
    }

    /**
     * Graceful fallback for unexisting methods.
     *
     * @param list<mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        throw new LogicException(\sprintf('Cannot call "%s" on "%s" type.', $method, $this));
    }
}
