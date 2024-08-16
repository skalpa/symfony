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

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @experimental
 */
abstract class Type implements \Stringable
{
    use TypeFactoryTrait;

    abstract public function getTypeIdentifier(): TypeIdentifierInterface;

    abstract public function asNonNullable(): self;

    abstract public function isNullable(): bool;

    /**
     * Get all the classes/interfaces implemented by an "object" type.
     */
    abstract public function getClassNames(): array;

    abstract public function accepts(Type $type): bool;

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
