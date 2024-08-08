<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo\Type;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * Represents a type composed by several other types.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 *
 * @template TPrimitive of TypeIdentifier
 * @template T of Type
 *
 * @extends TypeInterface<TPrimitive, Type>
 *
 * @experimental
 */
interface CompositeTypeInterface extends TypeInterface
{
    /**
     * Returns the list of subtypes that compose this type.
     *
     * @return list<T>
     */
    public function getTypes(): array;

    /**
     * Returns the list of subtypes that satisfy a given predicate.
     *
     * @param callable(T): bool $callable
     * @return list<T>
     */
    public function filter(callable $callable): array;

    /**
     * Checks whether at least one subtype accepts the given type.
     */
    public function anyAccepts(Type $type): bool;

    /**
     * Checks whether all subtypes accept the given type.
     */
    public function allAccept(Type $type): bool;
}
